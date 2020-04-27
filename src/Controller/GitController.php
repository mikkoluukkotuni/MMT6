<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Http\Client;
use Cake\Utility\Security;


// This uses Github's API v4
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
            
            // Use openssl encryption and base64 encoding to store the token in database
            $key = 'Lk5Uz3slx3BrAghS1aaW5AYgWZRV0tIX5eI0yPchFz4=';
            $git->token = base64_encode(Security::encrypt($git->token, $key));
            
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
        $key = 'Lk5Uz3slx3BrAghS1aaW5AYgWZRV0tIX5eI0yPchFz4=';

        if ($this->request->is(['patch', 'post', 'put'])) {
            $newGit = $this->request->data;

            // Use old token value if the field is left empty in the edit form
            if ($newGit['token'] == "") {                
                $newGit['token'] = Security::decrypt(base64_decode($git->token), $key);
            }

            $git = $this->Git->patchEntity($git, $newGit);            
            $git->token = base64_encode(Security::encrypt($git->token, $key));
            if ($this->Git->save($git)) {
                $this->Flash->success(__('Git configuration has been saved.'));
                
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Git configuration could not be saved. Please, try again.'));
            }
        }
        $this->set('git', $git);        
    }
    
    public function delete($id = null)
    {
        $git = $this->Git->get($id);
        $this->Git->delete($git);
        $this->Flash->success(__('Git configuration has been deleted.'));
        
        return $this->redirect(['action' => 'index']);
        
    }
}
