import { DomicilioDTO } from "../domicilios/modelo/domicilioDTO";

/**
 * DTO de creación para una tasación in situ.
 * - Corresponde a TasacionInSituCreacionDTO (PHP)
 */
export interface TasacionInSituCreacionDTO {
  tadId: number;                         // Id de la tasación digital asociada
  domicilio: DomicilioDTO;               // Domicilio donde se realizará la tasación
  tisFechaTasInSituProvisoria: string;   // Fecha provisoria para la tasación in situ
}

/**
 * DTO completo de una tasación in situ.
 * - Corresponde a TasacionInSituDTO (PHP)
 */
export interface TasacionInSituDTO {
  tisId: number;                               // Id de la tasación in situ
  tadId: number;                               // Id de la tasación digital asociada
  domicilio: DomicilioDTO;                     // Domicilio de la tasación in situ
  tisFechaTasInSituSolicitada: string;         // Fecha solicitada para la tasación in situ
  tisFechaTasInSituProvisoria: string;         // Fecha provisoria para la tasación in situ
  tisFechaTasInSituRealizada?: string | null;  // Fecha en que se realizó (opcional)
  tisFechaTasInSituRechazada?: string | null;  // Fecha en que se rechazó (opcional)
  tisObservacionesInSitu?: string | null;      // Observaciones (opcional)
  tisPrecioInSitu?: number | null;             // Precio de la tasación in situ (opcional)
}