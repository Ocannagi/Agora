import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';
import { ChangeDetectionStrategy, Component, computed, DestroyRef, inject, model } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { MatTooltipModule } from '@angular/material/tooltip'; // <-- tooltip
import { ImagenAntiguedadDTO } from '../modelo/ImagenAntiguedadDTO';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { ImagenesAntiguedadService } from '../imagenes-antiguedad-service';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { SwalDirective } from '@sweetalert2/ngx-sweetalert2';

@Component({
  selector: 'app-lista-imagenes-dto',
  imports: [DragDropModule, MatIconModule, MostrarErrores, SwalDirective, MatTooltipModule], // <-- agregar aquí
  templateUrl: './lista-imagenes-dto.html',
  styleUrl: './lista-imagenes-dto.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ListaImagenesDto {
  readonly arrayImagenes = model.required<ImagenAntiguedadDTO[]>();

  // Índice del ítem sobre el que se está “entrando” con el drag
  private lastEnteredIndex: number | null = null;


  #imgService = inject(ImagenesAntiguedadService);
  #destroyRef = inject(DestroyRef);

  readonly errores = computed(() => {
    const deleteError = this.#imgService.deleteError();
    return deleteError ? [deleteError] : [];
  });


  readonly previews = computed(() => {
    // Importante: se respeta el orden actual del array (el padre debe proveerlo según imaOrden inicialmente)
    return this.arrayImagenes().map(i => ({
      id: i.imaId,
      name: i.imaNombreArchivo,
      url: i.imaUrl
    }));
  });



  constructor() {

    this.#destroyRef.onDestroy(() => {
      this.#imgService.deleteError.set(null);
    });
  }

  // Reordenar la lista y actualizar imaOrden según la nueva posición
  protected onReorder(event: CdkDragDrop<ImagenAntiguedadDTO[]>) {
    const arr = [...this.arrayImagenes()];

    // Si no se registró enter en ningún ítem (fallback), usar currentIndex del evento
    let targetIndex = (this.lastEnteredIndex ?? event.currentIndex);
    // Saneo de límites
    targetIndex = Math.max(0, Math.min(targetIndex, arr.length - 1));

    moveItemInArray(arr, event.previousIndex, targetIndex);

    // Recalcular el orden (0..n-1) para reflejar lo que ve el usuario
    const reordenado = this.recalcularOrden(arr);
    this.arrayImagenes.set(reordenado);

    // Limpiar estado
    this.lastEnteredIndex = null;
  }

  // Eliminar una imagen por índice y normalizar el orden resultante
  protected eliminar(idx: number, imaId: number) {
    const arr = [...this.arrayImagenes()];
    arr.splice(idx, 1);
    this.arrayImagenes.set(this.recalcularOrden(arr));
    this.#imgService.delete(imaId).pipe(takeUntilDestroyed(this.#destroyRef)).subscribe({});
  }

  // Registrar en qué ítem está entrando el drag
  protected onDragEntered(index: number) {
    this.lastEnteredIndex = index;
  }

  // Normaliza imaOrden para que coincida con el índice actual en la lista
  private recalcularOrden(arr: ImagenAntiguedadDTO[]): ImagenAntiguedadDTO[] {
    // Crear nuevas referencias para mantener inmutabilidad
    return arr.map((img, idx) => ({
      ...img,
      imaOrden: idx + 1 // Orden empieza en 1
    }));
  }
}
