import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatDialogTitle, MatDialogContent, MatDialogActions, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { UploadImagenesAntiguedad } from "../upload-imagenes-antiguedad/upload-imagenes-antiguedad";
import { SwalDirective } from "@sweetalert2/ngx-sweetalert2";

export interface DialogData {
  arrayNameImgExistentes: string[];
}

@Component({
  selector: 'app-dialog-imagenes-antiguedad-upload',
  imports: [MatFormFieldModule,
    MatInputModule,
    FormsModule,
    MatButtonModule,
    MatDialogTitle,
    MatDialogContent,
    MatDialogActions,
    UploadImagenesAntiguedad,
    SwalDirective],
  templateUrl: './dialog-imagenes-antiguedad-upload.html',
  styleUrl: './dialog-imagenes-antiguedad-upload.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class DialogImagenesAntiguedadUpload {

  #dialogRef = inject(MatDialogRef<DialogImagenesAntiguedadUpload>);
  readonly data = inject<DialogData>(MAT_DIALOG_DATA);
  readonly arrayNameImgExistentes = signal<string[]>(this.data.arrayNameImgExistentes);
  readonly arrayImgFiles = signal<File[]>([]);


  

  // Cierra el diálogo solo si el usuario confirma en SweetAlert
  protected onConfirmSwal() {
    // pasar el array de archivos al cerrar
    this.#dialogRef.close(this.arrayImgFiles());
  }

  protected Cancelar() {
    this.#dialogRef.close();
  }

}
