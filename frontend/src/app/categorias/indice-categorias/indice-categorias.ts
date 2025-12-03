import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { IndiceEntidad } from "../../compartidos/componentes/indice-entidad/indice-entidad";
import { CategoriasService } from '../categorias-service';

@Component({
  selector: 'app-indice-categorias',
  imports: [MostrarErrores, IndiceEntidad],
  templateUrl: './indice-categorias.html',
  styleUrl: './indice-categorias.scss',
    providers: [
        {
          provide: SERVICIO_PAGINADO_TOKEN, useClass: CategoriasService
        },
        IndiceEntidadStore
      ],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceCategorias {
  protected store = inject(IndiceEntidadStore);
  constructor() {  
    this.store.setTitulo('Categorías');
    this.store.setPathCrear('/categorias/crear');
}
}