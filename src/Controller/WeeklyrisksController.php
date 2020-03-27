<?php

namespace App\Controller;

use App\Controller\AppController;
use App\Controller\WeeklyreportsController;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity;
use Cake\I18n\Time;

class WeeklyrisksController extends AppController {

        public function edit($id = null) {
        
        $risk = $this->Weeklyrisks->get($id);
        $this->request->session()->write('selected_risk_description', TableRegistry::get('Risks')->get($risk->risk_id)['description']);
        $wr_id = $risk->weeklyreport_id;
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $risk = $this->Weeklyrisks->patchEntity($risk, $this->request->data);
            $time = Time::now();
            $risk['date'] = $time;
            if ($this->Weeklyrisks->save($risk)) {
                $this->Flash->success(__('The risk has been saved.'));
                (new WeeklyreportsController())->edit($wr_id);
                return $this->redirect(['controller' => 'weeklyreports', 'action' => 'view', $wr_id]); 
            } else {
                $this->Flash->error(__('The risk could not be saved. Please, try again.'));
            }
        }
        
        $risksController = new RisksController();
        
        $types = $risksController->getImpactProbTypes();
        
        $this->set(compact('risk', 'types','editable'));
        $this->set('_serialize', ['risk']);
        
    }

}
