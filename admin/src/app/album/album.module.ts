import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgZorroAntdModule } from 'ng-zorro-antd';

import { SharedModule } from '~/shared/shared.module';
import { AlbumRoutingModule } from './album-routing.module';
import { AlbumComponent } from './album.component';
import { FormsModule } from '@angular/forms';

@NgModule({
  imports: [
    CommonModule,
    NgZorroAntdModule,
    SharedModule,
    FormsModule,
    AlbumRoutingModule
  ],
  declarations: [AlbumComponent]
})
export class AlbumModule { }
