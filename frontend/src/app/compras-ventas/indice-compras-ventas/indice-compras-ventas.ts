import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidad } from '../../compartidos/componentes/indice-entidad/indice-entidad';
import { ComprasVentasService } from '../compras-ventas-service';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';

@Component({
  selector: 'app-indice-compras-ventas',
  imports: [MostrarErrores, IndiceEntidad],
  templateUrl: './indice-compras-ventas.html',
  styleUrl: './indice-compras-ventas.scss',
  providers: [
    { provide: SERVICIO_PAGINADO_TOKEN, useClass: ComprasVentasService },
    IndiceEntidadStore
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceComprasVentas {
  protected store = inject(IndiceEntidadStore);

  constructor() {
    this.store.setTitulo('Mis compras');
    // No hay creación manual por ahora; dejamos vacío o una ruta futura si corresponde
    this.store.setPathCrear('');
    // Columnas adicionales planas del DTO índice (además de id, nombre, acciones)
    this.store.setColumnasExtras(['covTipoMedioPago']);
  }
}
