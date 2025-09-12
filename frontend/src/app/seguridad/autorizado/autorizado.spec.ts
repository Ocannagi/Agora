import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Autorizado } from './autorizado';

describe('Autorizado', () => {
  let component: Autorizado;
  let fixture: ComponentFixture<Autorizado>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Autorizado]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Autorizado);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
