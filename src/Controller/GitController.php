<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Http\Client;


// This uses Github's API v3
// Actual API call happens in MetricController's function addmultiple()

class GitController extends AppController 
{
    public function index() 
    {
        
        $project_id = $this->request->session()->read('selected_project')['id'];
        
        $git = $this->Git->find('all', ['conditions' => ['project_id' => $project_id]])->first();
        
        $this->set('git', $git);        
    }
    
    public function add()
    {        
        $git = $this->Git->newEntity();
        
        if ($this->request->is('post')) {
           
            // get data from the form
            $git = $this->Git->patchEntity($git, $this->request->data);  
            
            $git['project_id'] = $this->request->session()->read('selected_project')['id'];
            
            if ($this->Git->save($git)) {
                $this->Flash->success(__('Git configuration has been added.'));
                
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Git configuration could not be added. Please, try again.'));
            }
            
            return $this->redirect(['action' => 'index']);
        }        
    }
    
    public function edit($id = null)
    {        
        $git = $this->Git->get($id);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $git = $this->Git->patchEntity($git, $this->request->data);
            if ($this->Git->save($git)) {
                $this->Flash->success(__('Git configuration has been saved.'));
                
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Git configuration could not be saved. Please, try again.'));
            }
        }
        $this->set('git', $git);        
    }
    
    // public function delete($id = null)
    // {
        
    //     $git = $this->Git->get($id);
        
//
//            if ($this->Git->delete($git)) {
//                $this->Flash->success(__('Git configuration has been deleted.'));
//            } else {
//                $this->Flash->error(__('Git configuration could not be deleted. Please, try again.'));
//            }
        
    //     return $this->redirect(['action' => 'index']);
        
    // }
}
