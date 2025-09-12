import { Component, input } from '@angular/core';

@Component({
  selector: 'app-autorizado',
  imports: [],
  templateUrl: './autorizado.html',
  styleUrl: './autorizado.scss'
})
export class Autorizado {
  public esAutorizado = input.required<boolean>();
}
