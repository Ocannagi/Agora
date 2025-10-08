import { DomicilioDTO } from "../../domicilios/modelo/domicilioDTO";

export interface UsuarioDTO {
  usrId: number;                       // Identificador único
  usrDni: string;                      // DNI
  usrApellido: string;                 // Apellido
  usrNombre: string;                   // Nombre
  usrRazonSocialFantasia: string | null; // Razón social / fantasía (nullable)
  usrCuitCuil: string | null;          // CUIT/CUIL (nullable)
  usrTipoUsuario: string;              // Tipo de usuario
  usrMatricula: string | null;         // Matrícula (nullable)
  domicilio: DomicilioDTO;             // Domicilio
  usrFechaNacimiento: string;          // Fecha de nacimiento (ISO o yyyy-MM-dd)
  usrDescripcion: string | null;       // Descripción (nullable)
  usrScoring: number;                  // Puntuación
  usrEmail: string;                    // Email
  usrPassword: string;                 // Password (considerar no exponer en UI)
}
