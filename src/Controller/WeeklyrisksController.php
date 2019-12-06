<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity;

class WeeklyrisksController extends AppController {

        public function edit($id = null) {
        
        $risk = $this->Weeklyrisks->get($id);
        $this->request->session()->write('selected_risk_description', TableRegistry::get('Risks')->get($risk->risk_id)['description']);
        $wr_id = $risk->weeklyreport_id;
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $risk = $this->Weeklyrisks->patchEntity($risk, $this->request->data);
            if ($this->Weeklyrisks->save($risk)) {
                $this->Flash->success(__('The risk has been saved.'));
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
