import { ChangeDetectionStrategy, Component, effect, signal, computed, input } from '@angular/core';
import { model } from '@angular/core';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MostrarErrores } from "../../compartidos/componentes/mostrar-errores/mostrar-errores";
import { ListaImagenesFile } from "../lista-imagenes-file/lista-imagenes-file";
import { MAX_IMG_ANTIGUEDAD } from '../feautures';

@Component({
  selector: 'app-upload-imagenes-antiguedad',
  standalone: true,
  imports: [DragDropModule, MatIconModule, MatButtonModule, MostrarErrores, ListaImagenesFile],
  templateUrl: './upload-imagenes-antiguedad.html',
  styleUrl: './upload-imagenes-antiguedad.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class UploadImagenesAntiguedad {
  // Model con el array de archivos en el orden elegido por el usuario
  readonly arrayImgFiles = model<File[]>([]);
  // Input signal: SIEMPRE leer con this.arrayNameImgExistentes()
  readonly arrayNameImgExistentes = input<string[]>([]);


  // Cantidad de imágenes previas existentes (leer el signal)
  readonly ImgPreviosCount = computed(() => this.arrayNameImgExistentes().length);

  readonly hayImgPrevias = computed(() => this.ImgPreviosCount() > 0);
  readonly esEdicion = computed(() => this.hayImgPrevias());

  // Configuración de validación
  private readonly maxFiles = MAX_IMG_ANTIGUEDAD;
  // Clamp a 0 para evitar negativos
  private readonly maxNewFiles = computed(() => Math.max(0, this.maxFiles - this.ImgPreviosCount()));
  private readonly maxBytes = 200_000; // 200 KB
  private readonly allowedTypes = new Set(['image/jpeg', 'image/png', 'image/gif']);

  // Estado UI
  readonly isDragOver = signal(false);
  readonly errores = signal<string[]>([]);


  constructor() {

    console.log('arrayNameImgExistentes', this.arrayNameImgExistentes());
    // Limpiar errores al cambiar los archivos
    effect(() => {
      this.arrayImgFiles();
      this.errores.set([]);
    });
    
  }


  // Arrastrar/soltar en la zona de carga
  protected onDragOver(event: DragEvent) {
    event.preventDefault();
    this.isDragOver.set(true);
  }
  protected onDragLeave(event: DragEvent) {
    event.preventDefault();
    this.isDragOver.set(false);
  }
  protected onDrop(event: DragEvent) {
    event.preventDefault();
    this.isDragOver.set(false);
    if (!event.dataTransfer) return;
    this.procesarArchivos(event.dataTransfer.files);
  }

  // Selección por input
  protected onFileInputChange(event: Event) {
    const inputEl = event.target as HTMLInputElement;
    if (!inputEl.files) return;
    this.procesarArchivos(inputEl.files);
    inputEl.value = ''; // permite volver a seleccionar los mismos archivos
  }

  // Valida y agrega archivos al model
  private procesarArchivos(fileList: FileList | File[]) {
    const actuales = [...this.arrayImgFiles()];
    const nuevos = Array.from(fileList);
    // Leer el input signal y asegurar iterable
    const existentes = [...(this.arrayNameImgExistentes() ?? [])];

    const errs: string[] = [];
    const agregables: File[] = [];

    // No superar cantidad máxima (considerando los actuales)
    if (actuales.length >= this.maxNewFiles()) {
      let msg = `No puedes subir más de ${this.maxFiles} archivos en total.`;
      if (this.hayImgPrevias())
        msg += ` Solo puedes subir ${this.maxNewFiles()} archivos nuevos.`;
      errs.push(msg);
    } else {
      for (const f of nuevos) {
        if (!this.allowedTypes.has(f.type)) {
          errs.push(`Tipo no permitido: ${f.name} (${f.type || 'desconocido'}). Solo JPEG, PNG o GIF.`);
          continue;
        }
        if (f.size > this.maxBytes) {
          errs.push(`El archivo ${f.name} supera los ${this.maxBytes / 1000} KB.`);
          continue;
        }
        // Evitar duplicados por nombre (en actuales, en agregables y en existentes)
        const dup = actuales.some(a => a.name === f.name)
          || agregables.some(a => a.name === f.name)
          || existentes.some(e => e === f.name);
        if (dup) continue;

        agregables.push(f);

        // Si al agregar excede el máximo, corta
        if (actuales.length + agregables.length >= this.maxNewFiles()) break;
      }
    }

    if (agregables.length) {
      this.arrayImgFiles.set([...actuales, ...agregables]);
    }
    this.errores.set(errs);
  }
}
