import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgZorroAntdModule } from 'ng-zorro-antd';

import { SharedModule } from '~/shared/shared.module';
import { CourseCreateRoutingModule } from './course-create-routing.module';
import { CourseCreateComponent } from './course-create.component';

import { FormsModule } from '@angular/forms';
import { NgxTinymceModule } from 'ngx-tinymce';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    NgZorroAntdModule,
    SharedModule,
    NgxTinymceModule.forRoot({
        baseURL: '//cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.0/',
    }),
    CourseCreateRoutingModule
  ],
  declarations: [CourseCreateComponent]
})
export class CourseCreateModule { }
