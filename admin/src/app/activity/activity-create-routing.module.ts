import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ActivityCreateComponent } from './activity-create.component';

const routes: Routes = [
  {
    path: 'backend/activity/create',
    component: ActivityCreateComponent
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ActivityCreateRoutingModule { }
