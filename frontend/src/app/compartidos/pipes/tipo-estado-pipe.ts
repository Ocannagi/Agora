import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'tipoEstado'
})
export class TipoEstadoPipe implements PipeTransform {
  private readonly estadoMap: Record<string, string> = {
    'RD': 'RD - Retirado Disponible',
    'RN': 'RN - Retirado No disponible',
    'VE': 'VE - A la Venta',
    'CO': 'CO - Comprado',
    'TD': 'TD - Descripción',
    'TI': 'TI - Descripción'
  };

  transform(tipoEstado: string): string {
    return this.estadoMap[tipoEstado] || 'Desconocido';
  }
}