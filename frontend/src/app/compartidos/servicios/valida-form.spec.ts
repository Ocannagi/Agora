import { TestBed } from '@angular/core/testing';

import { ValidaForm } from './valida-form';

describe('ValidaForm', () => {
  let service: ValidaForm;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ValidaForm);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
