import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ActivityListComponent } from './activity-list.component';

const routes: Routes = [
  {
    path: 'backend/activity',
    component: ActivityListComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ActivityListRoutingModule { }
