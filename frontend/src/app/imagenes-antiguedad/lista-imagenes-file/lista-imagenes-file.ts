import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';
import { ChangeDetectionStrategy, Component, computed, effect, input, model, signal } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-lista-imagenes-file',
  imports: [DragDropModule, MatIconModule],
  templateUrl: './lista-imagenes-file.html',
  styleUrl: './lista-imagenes-file.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ListaImagenesFile {
  readonly arrayImgFiles = model.required<File[]>();
  readonly esEdicion = input<boolean>(false);

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


  // Elimina un archivo por índice
  protected eliminar(idx: number) {
    const arr = [...this.arrayImgFiles()];
    arr.splice(idx, 1);
    this.arrayImgFiles.set(arr);
  }

  // Se dispara cuando el drag entra en un ítem de la lista
  protected onDragEntered(index: number) {
    this.lastEnteredIndex = index;
  }

}
