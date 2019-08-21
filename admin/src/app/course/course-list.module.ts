import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgZorroAntdModule } from 'ng-zorro-antd';

import { SharedModule } from '~/shared/shared.module';
import { CourseListRoutingModule } from './course-list-routing.module';
import { CourseListComponent } from './course-list.component';

@NgModule({
  imports: [
    CommonModule,
    NgZorroAntdModule,
    SharedModule,
    CourseListRoutingModule
  ],
  declarations: [CourseListComponent]
})
export class CourseListModule { }
