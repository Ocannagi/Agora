import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { IndiceEntidad } from '../../compartidos/componentes/indice-entidad/indice-entidad';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { VentasDetalleService } from '../ventas-detalle-service';
import { AutenticacionStore } from '../../seguridad/store/autenticacion.store';

@Component({
  selector: 'app-indice-ventas',
  imports: [MostrarErrores, IndiceEntidad],
  templateUrl: './indice-ventas.html',
  styleUrl: './indice-ventas.scss',
  providers: [
    { provide: SERVICIO_PAGINADO_TOKEN, useClass: VentasDetalleService },
    IndiceEntidadStore
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceVentas {
  protected store = inject(IndiceEntidadStore);
  #authService = inject(AutenticacionStore);

  constructor() {
    const titulo = this.#authService.isSoporteTecnico()?  'Todas las Ventas' : 'Mis Ventas';
    this.store.setTitulo(titulo);
    // No hay creación manual por ahora; dejamos vacío o una ruta futura si corresponde
    this.store.setPathCrear('');
    // Columnas adicionales planas del DTO índice (además de id, nombre, acciones)
    const colExtra = this.#authService.isSoporteTecnico() ? ['extra','covTipoMedioPago'] : ['covTipoMedioPago'];
    this.store.setColumnasExtras(colExtra);
  }

}
