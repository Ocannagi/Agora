import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { IndiceEntidad } from '../../compartidos/componentes/indice-entidad/indice-entidad';
import { MostrarErrores } from '../../compartidos/componentes/mostrar-errores/mostrar-errores';
import { IndiceEntidadStore } from '../../compartidos/componentes/indice-entidad/store-indice-entidad/indice-entidad.store';
import { SERVICIO_PAGINADO_TOKEN } from '../../compartidos/proveedores/tokens';
import { AntiguedadesVentaService } from '../antiguedades-venta-service';

@Component({
  selector: 'app-indice-antiguedades-venta',
  imports: [MostrarErrores, IndiceEntidad],
  templateUrl: './indice-antiguedades-venta.html',
  styleUrl: './indice-antiguedades-venta.scss',
  providers: [
    { provide: SERVICIO_PAGINADO_TOKEN, useClass: AntiguedadesVentaService },
    IndiceEntidadStore
  ],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class IndiceAntiguedadesVenta {
  // Store inyectado para configurar el índice
  protected store = inject(IndiceEntidadStore);

  constructor() {
    // Título y acciones del índice
    this.store.setTitulo('Antigüedades en venta');
    this.store.setPathCrear('/antiguedadesAlaVenta/crear');

    // Columnas extras a mostrar (campos planos del DTO)
    this.store.setColumnasExtras(['aavPrecioVenta', 'aavFechaPublicacion']);
  }
}
