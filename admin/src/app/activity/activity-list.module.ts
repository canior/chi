import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgZorroAntdModule } from 'ng-zorro-antd';

import { SharedModule } from '~/shared/shared.module';
import { ActivityListRoutingModule } from './activity-list-routing.module';
import { ActivityListComponent } from './activity-list.component';

@NgModule({
  imports: [
    CommonModule,
    NgZorroAntdModule,
    SharedModule,
    ActivityListRoutingModule
  ],
  declarations: [ActivityListComponent]
})
export class ActivityListModule { }
