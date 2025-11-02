import { environment } from "../../environments/environment.development";

 export function normalizarUrlImagen(url: string): string {
    
  // Ya absoluta
    if (/^https?:\/\//i.test(url)) return url;

    // Limpieza de prefijos './' o '/' redundantes
    const path = url.replace(/^[.\/]+/, '');

    // Construir URL absoluta usando storageURL del entorno
    const base = environment.storageURL;
    if (base && base !== '...') {
      const baseWithSlash = base.endsWith('/') ? base : base + '/';
      return new URL(path, baseWithSlash).toString();
    }

    // Fallback: relativa al origen actual (puede fallar en :4200 si no hay proxy)
    return '/' + path;
  }