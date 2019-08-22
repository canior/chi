import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { CategoryCreateComponent } from './category-create.component';

const routes: Routes = [
  {
    path: 'backend/category/create',
    component: CategoryCreateComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class CategoryCreateRoutingModule { }
