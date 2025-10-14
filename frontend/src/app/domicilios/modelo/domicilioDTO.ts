import { LocalidadDTO } from "../../localidades/modelo/localidadDTO";

export interface DomicilioCreacionDTO {
  domCPA: string;
  domCalleRuta: string;
  domNroKm: number;
  domPiso: string | null;
  domDepto: string | null;
  locId: number;
}

export interface DomicilioDTO {
  domId: number;
  domCPA: string;
  domCalleRuta: string;
  domNroKm: number;
  domPiso: string | null;
  domDepto: string | null;
  localidad: LocalidadDTO;
}