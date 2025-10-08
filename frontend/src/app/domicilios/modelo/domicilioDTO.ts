import { LocalidadDTO } from "../../localidades/modelo/localidadDTO";

export interface DomicilioDTO {
  domId: number;
  domCPA: string;
  domCalleRuta: string;
  domNroKm: number;
  domPiso: string | null;
  domDepto: string | null;
  localidad: LocalidadDTO; 
}