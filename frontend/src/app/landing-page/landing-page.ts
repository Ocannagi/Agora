import { ChangeDetectionStrategy, Component } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIcon } from "@angular/material/icon";
import { NgOptimizedImage } from '@angular/common';

@Component({
  selector: 'app-landing-page',
  imports: [MatIcon, MatButtonModule, NgOptimizedImage],
  templateUrl: './landing-page.html',
  styleUrl: './landing-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class LandingPage {

//readonly heroUrl = new URL('./imagesLanding/hero.jpg', import.meta.url).toString();


}
