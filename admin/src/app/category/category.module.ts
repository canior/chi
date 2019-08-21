import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgZorroAntdModule } from 'ng-zorro-antd';

import { SharedModule } from '~/shared/shared.module';
import { CategoryRoutingModule } from './category-routing.module';
import { CategoryComponent } from './category.component';
import { FormsModule } from '@angular/forms';
import { NgxTinymceModule } from 'ngx-tinymce';

@NgModule({
  imports: [
    CommonModule,
    NgZorroAntdModule,
    SharedModule,
    FormsModule,
    NgxTinymceModule.forRoot({
        baseURL: '//cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.0/',
    }),
    CategoryRoutingModule
  ],
  declarations: [CategoryComponent]
})
export class CategoryModule { }
