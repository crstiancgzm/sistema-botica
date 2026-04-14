<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivosService
{
	/**
	 * Sincroniza archivos polimórficos (create/update/delete) para modelos con gid.
	 */
	public function syncArchivos(Request $request, Model $model, string $relation, string $folder, array &$uploadedFiles): void
	{
		$existingArchivos = $model->$relation ? $model->$relation->pluck('id')->toArray() : [];
		$updatedArchivos = [];

		// Soporta datos anidados bajo '{parent}.archivos' o directamente 'archivos'
		$relationPath = $relation;
		foreach (array_keys($request->all()) as $parentKey) {
			$nested = $request->input("$parentKey.$relation");
			if (is_array($nested)) {
				$relationPath = "$parentKey.$relation";
				break;
			}
		}

		if ($request->has($relationPath)) {
			$archivos = $request->input($relationPath);

			if (is_array($archivos)) {
				foreach ($archivos as $key => $archivo) {
					if (isset($archivo['id']) && in_array($archivo['id'], $existingArchivos)) {
						$existing = $model->$relation()->find($archivo['id']);
						if ($existing) {
							$existing->update([
								'nombre'  => $archivo['nombre'] ?? $existing->nombre,
								'tipo_1'  => $archivo['tipo_1'] ?? $existing->tipo_1,
								'tipo_2'  => $archivo['tipo_2'] ?? $existing->tipo_2,
								'aux_1'   => $archivo['aux_1'] ?? $existing->aux_1,	
								'aux_2'   => $archivo['aux_2'] ?? $existing->aux_2,
							]);
						}
						$updatedArchivos[] = $archivo['id'];
					} else {
						// Leer archivo subido respetando ruta anidada
						$fileKey = "$relationPath.$key.url";
						if ($request->hasFile($fileKey)) {
							$file = $request->file($fileKey);
							$baseName = isset($archivo['nombre']) ? $archivo['nombre'] : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
							$sanitizedName = str_replace(' ', '_', $baseName);
							$extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'jpg');
							$filename = $sanitizedName . '.' . $extension;

							// Guardar usando el nombre saneado en el disco público
							Storage::disk('public')->putFileAs($folder, $file, $filename);
							$path = $folder . '/' . $filename;

							$uploadedFiles[] = $path;

							$new = $model->$relation()->create([
								'archivable_id'   => $model->id,
								'archivable_type' => get_class($model),
								'nombre'          => $sanitizedName,
								'url'             => $path,
								'tipo_1'          => $archivo['tipo_1'] ?? null,
								'tipo_2'          => $archivo['tipo_2'] ?? null,
								'aux_1'           => $archivo['aux_1'] ?? null,
								'aux_2'           => $archivo['aux_2'] ?? null,
							]);

							$updatedArchivos[] = $new->id;
						}
					}
				}
			}
		}

		$archivosToDelete = array_diff($existingArchivos, $updatedArchivos);
		if (!empty($archivosToDelete)) {
			$archivos = $model->$relation()->whereIn('id', $archivosToDelete)->get();
			foreach ($archivos as $archivo) {
				Storage::disk('public')->delete($archivo->url);
				$archivo->delete();
			}
		}
	}
}