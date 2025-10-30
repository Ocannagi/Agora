import { ChangeDetectionStrategy, Component, computed, inject, input, signal, linkedSignal } from '@angular/core';
import { Router } from '@angular/router';
import { Location } from '@angular/common';
import { CurrencyPipe, NgOptimizedImage } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { AntiguedadALaVentaDTO } from '../../antiguedades-venta/modelo/AntiguedadAlaVentaDTO';
import { numberAttributeOrNull } from '../../compartidos/funciones/transform';
import { SearchWordStore } from '../galeria-vertical/store-search-word/search-word.store';
import { CarritoStore } from '../../carrito/store-carrito/carrito.store';
import { ImagenAntiguedadDTO } from '../../imagenes-antiguedad/modelo/ImagenAntiguedadDTO';

@Component({
  selector: 'app-ver-antiguedad-galeria',
  imports: [MatCardModule, MatButtonModule, NgOptimizedImage, CurrencyPipe],
  templateUrl: './ver-antiguedad-galeria.html',
  styleUrl: './ver-antiguedad-galeria.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class VerAntiguedadGaleria {
  readonly id = input.required({ transform: numberAttributeOrNull });

  readonly storeSearch = inject(SearchWordStore);
  readonly storeCarrito = inject(CarritoStore);
  readonly router = inject(Router);
  readonly location = inject(Location);

  // Estado de navegación
  private readonly navState = this.location.getState() as { returnTo?: unknown } | null;
  private readonly returnTo = signal<string | null>(typeof this.navState?.returnTo === 'string' ? (this.navState!.returnTo as string) : null);


  readonly selectedAntiguedad = computed<AntiguedadALaVentaDTO | null>(() => {
    const id = this.id();
    if (!Number.isFinite(id)) return null;
    return this.storeSearch.arrayEntidad().find(x => x.aavId === id) ?? null;
  });

  readonly images = computed(() => this.selectedAntiguedad()?.antiguedad.imagenes ?? []);


  readonly selectedImage = linkedSignal<ImagenAntiguedadDTO[], ImagenAntiguedadDTO | null>({
    source: this.images,
    computation: (imgs, prev) => {
      const previous = prev?.value ?? null;
      if (previous && imgs.includes(previous)) return previous;
      return imgs[0] ?? null;
    }
  })

  readonly yaEstaEnElCarrito = computed(() => {
    const aavId = this.selectedAntiguedad()?.aavId;
    if (!aavId) return false;
    return this.storeCarrito.isInCarrito(aavId);
  });

  readonly noEstaEnElCarrito = computed(() => !this.yaEstaEnElCarrito());


  // Acciones UI
  selectThumb(index: number): void {
    this.selectedImage.set(this.images()[index] ?? null);
  }

  // Acciones
  back(): void {
    const rt = this.returnTo();
    if (rt) {
      this.router.navigateByUrl(rt);
    } else {
      this.location.back(); // fallback: volver un paso
    }
  }

  addToCart(): void {
    const antiguedad = this.selectedAntiguedad();
    if (antiguedad) {
      this.storeCarrito.addCarrito(antiguedad);
    }
    this.back();
  }
}
