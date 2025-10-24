import { ChangeDetectionStrategy, Component, effect, signal, computed } from '@angular/core';
import { model } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DragDropModule, CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";

@Component({
  selector: 'app-upload-imagenes-antiguedad',
  standalone: true,
  imports: [CommonModule, DragDropModule, MatIconModule, MatButtonModule, MostrarErrores],
  templateUrl: './upload-imagenes-antiguedad.html',
  styleUrl: './upload-imagenes-antiguedad.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class UploadImagenesAntiguedad {
  // Model con el array de archivos en el orden elegido por el usuario
  readonly arrayImgFiles = model<File[]>([]);

  // Configuración de validación
  private readonly maxFiles = 5;
  private readonly maxBytes = 200_000; // 200 KB
  private readonly allowedTypes = new Set(['image/jpeg', 'image/png', 'image/gif']);

  // Estado UI
  readonly isDragOver = signal(false);
  readonly errores = signal<string[]>([]);

  // índice del ítem sobre el que se está “entrando” con el drag
  private lastEnteredIndex: number | null = null;

  // Thumbnails (Object URLs) con cleanup automático
  readonly previews = computed(() => {
    const files = this.arrayImgFiles();
    return files.map(f => ({
      name: f.name,
      size: f.size,
      type: f.type,
      url: URL.createObjectURL(f)
    }));
  });

  // Revocar URLs previas para evitar memory leaks
  constructor() {
    let lastUrls: string[] = [];
    effect((onCleanup) => {
      const curr = this.previews().map(p => p.url);
      onCleanup(() => {
        lastUrls.forEach(u => URL.revokeObjectURL(u));
        lastUrls = curr;
      });
    });
  }

  // Arrastrar/soltar en la zona de carga
  protected onDragOver(event: DragEvent) {
    event.preventDefault();
    this.isDragOver.set(true);
  }
  protected onDragLeave(event: DragEvent) {
    event.preventDefault();
    this.isDragOver.set(false);
  }
  protected onDrop(event: DragEvent) {
    event.preventDefault();
    this.isDragOver.set(false);
    if (!event.dataTransfer) return;
    this.procesarArchivos(event.dataTransfer.files);
  }

  // Selección por input
  protected onFileInputChange(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;
    this.procesarArchivos(input.files);
    input.value = ''; // permite volver a seleccionar los mismos archivos
  }

  // Elimina un archivo por índice
  protected eliminar(idx: number) {
    const arr = [...this.arrayImgFiles()];
    arr.splice(idx, 1);
    this.arrayImgFiles.set(arr);
  }

  // Reordenar en la lista de previews usando el índice del ítem sobre el que se soltó
  protected onReorder(event: CdkDragDrop<File[]>) {
    const arr = [...this.arrayImgFiles()];
    // Si no se registró enter en ningún ítem (fallback), usar currentIndex del evento
    let targetIndex = (this.lastEnteredIndex ?? event.currentIndex);
    // saneo de límites
    targetIndex = Math.max(0, Math.min(targetIndex, arr.length - 1));

    moveItemInArray(arr, event.previousIndex, targetIndex);
    this.arrayImgFiles.set(arr);

    // limpiar estado
    this.lastEnteredIndex = null;
  }

  // Se dispara cuando el drag entra en un ítem de la lista
  protected onDragEntered(index: number) {
    this.lastEnteredIndex = index;
  }

  // Valida y agrega archivos al model
  private procesarArchivos(fileList: FileList | File[]) {
    const actuales = [...this.arrayImgFiles()];
    const nuevos = Array.from(fileList);

    const errs: string[] = [];
    const agregables: File[] = [];

    // No superar cantidad máxima (considerando los actuales)
    if (actuales.length >= this.maxFiles) {
      errs.push(`No puedes subir más de ${this.maxFiles} archivos.`);
    } else {
      for (const f of nuevos) {
        if (!this.allowedTypes.has(f.type)) {
          errs.push(`Tipo no permitido: ${f.name} (${f.type || 'desconocido'}). Solo JPEG, PNG o GIF.`);
          continue;
        }
        if (f.size > this.maxBytes) {
          errs.push(`El archivo ${f.name} supera los ${this.maxBytes / 1000} KB.`);
          continue;
        }
        // Evitar duplicados (por name)
        const dup = actuales.some(a => a.name === f.name) || agregables.some(a => a.name === f.name);
        if (dup) continue;

        agregables.push(f);

        // Si al agregar excede el máximo, corta
        if (actuales.length + agregables.length >= this.maxFiles) break;
      }
    }

    if (agregables.length) {
      this.arrayImgFiles.set([...actuales, ...agregables]);
    }
    this.errores.set(errs);
  }
}
