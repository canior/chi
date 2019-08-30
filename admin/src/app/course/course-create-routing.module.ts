import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { CourseCreateComponent } from './course-create.component';

const routes: Routes = [
  {
    path: 'backend/course/create',
    component: CourseCreateComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class CourseCreateRoutingModule { }
