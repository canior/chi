import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgZorroAntdModule } from 'ng-zorro-antd';

import { SharedModule } from '~/shared/shared.module';
import { CourseListRoutingModule } from './course-list-routing.module';
import { CourseListComponent } from './course-list.component';
import { FormsModule } from '@angular/forms';

@NgModule({
  imports: [
    CommonModule,
    NgZorroAntdModule,
    SharedModule,
    FormsModule,
    CourseListRoutingModule
  ],
  declarations: [CourseListComponent]
})
export class CourseListModule { }
