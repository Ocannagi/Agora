import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { IndiceEntidad } from "../../compartidos/componentes/indice-entidad/indice-entidad";
import { SubcategoriasService } from '../subcategorias-service';

@Component({
  selector: 'app-indice-subcategorias',
  imports: [MostrarErrores, IndiceEntidad],
  templateUrl: './indice-subcategorias.html',
  styleUrls: ['./indice-subcategorias.scss'],
      providers: [
          {
            provide: SERVICIO_PAGINADO_TOKEN, useClass: SubcategoriasService
          },
          IndiceEntidadStore
        ],
      changeDetection: ChangeDetectionStrategy.OnPush
})

export class IndiceSubcategorias {
  protected store = inject(IndiceEntidadStore);
  constructor() {  
    this.store.setTitulo('Subcategorías');
    this.store.setPathCrear('/subcategorias/crear');
}
}