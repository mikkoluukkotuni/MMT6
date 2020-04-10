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
        
    
    public function isAuthorized($user)
    {
        // Admin can access every action
        if (isset($user['role']) && $user['role'] === 'admin') {
            return true;
        }
        
        $project_role = $this->request->session()->read('selected_project_role');        
        $project_id = $this->request->session()->read('selected_project')['id'];
        
        //Only admin and manager can access trello pages
        $allowed = ($project_role == 'manager' || $project_role == 'admin');
        
        if ($allowed) {
            //One can only access trello pages of current project
            if(in_array ($this->request->action, ['links','edit','delete'])){
                
                //For CakePhP 3.4+
                //$trelloId = $this->request->getParam('pass')[0];              
                
                //For older CakePHP
                $trelloId = $this->request->param('pass')[0];                
                $checkItem = $this->Git->find()->where(['id' => $trelloId])->first();
                
                if ($checkItem != null) {
                    return $checkItem->project_id === $project_id;
                } else {
                    return false;
                }                
            } else {                
                return true;                
            }            
        }
    }
}
