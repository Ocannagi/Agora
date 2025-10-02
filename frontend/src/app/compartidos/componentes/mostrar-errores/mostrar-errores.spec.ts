import { ComponentFixture, TestBed } from "@angular/core/testing";
import { MostrarErrores } from "./mostrar-errores";


describe('MostrarErrores', () => {
  let component: MostrarErrores;
  let fixture: ComponentFixture<MostrarErrores>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ MostrarErrores ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(MostrarErrores);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should show a list-item for each error', () => {
    fixture.componentRef.setInput('errores', ['Error 1', 'Error 2', 'Error 3']);
    fixture.detectChanges();
    const items = fixture.nativeElement.querySelectorAll('li');
    expect(items.length).toBe(3);
  });
});
