import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidad } from '../../compartidos/componentes/indice-entidad/indice-entidad';
import { ComprasVentasService } from '../compras-ventas-service';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { AutenticacionStore } from '../../seguridad/store/autenticacion.store';

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
  #authService = inject(AutenticacionStore);

  constructor() {
    const titulo = this.#authService.isSoporteTecnico()?  'Todas las Compras' : 'Mis compras';
    this.store.setTitulo(titulo);
    // No hay creación manual por ahora; dejamos vacío o una ruta futura si corresponde
    this.store.setPathCrear('');
    // Columnas adicionales planas del DTO índice (además de id, nombre, acciones)

    const colExtra = this.#authService.isSoporteTecnico() ? ['extra','covTipoMedioPago'] : ['covTipoMedioPago'];

    this.store.setColumnasExtras(colExtra);
  }
}
