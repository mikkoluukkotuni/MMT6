<!-- This is the second page in the weeklyreport form.
     $current_metrics is what was previously placed in the form if the user visits this page a second time
-->
<?= $this->Html->script('trello.js') ?>

<!-- 
    If the lower navigation bar needed, links go here
    
    <ul class="side-nav">
        
    </ul>
-->

<div class="metrics form large-6 medium-12 columns content float: left">
    <?= $this->Form->create($metric) ?>
    <fieldset>
        <legend><?= __('Add Metrics, Page 2/4') ?></legend>
        <?php
            $current_metrics = $this->request->session()->read('current_metrics');
        
            echo $this->Form->input('phase', 
                array('value' => $current_metrics[0]['value'], 'label' => $metricNames[1],'type' => 'number', 'min' => 0, 'required' => true));
            
            echo $this->Html->image('../webroot/img/infoicon.png', ['alt' => 'infoicon', 'title' => 'This can be for example number of sprints.', 'class' => 'infoicon']);
            echo $this->Form->input('totalPhases', 
                array('value' => $current_metrics[1]['value'], 'label' => $metricNames[2],'type' => 'number', 'min' => 0, 'required' => true));
            ?>
            <div class="boxed">
                <p>
                    <?php echo "Current state of the requirements list"; ?>
                </p>
                <?php if ($trello != null): ?>
                <div id="trello-requirements"  data-board-id="<?= $trello->board_id ?>" data-app-key="<?= $trello->app_key ?>" data-token="<?= $trello->token ?>">
                    <?php foreach($trello->trellolinks as $link): ?>
                    <div class="trello-link" data-list-id="<?= $link->list_id ?>" data-req="<?= $link->requirement_type ?>"></div>
                    <?php endforeach; ?>
                </div>
                
                <?php 
                    endif;

                    echo $this->Html->image('../webroot/img/infoicon.png', ['alt' => 'infoicon', 'title' => 'Product backlog is a list of the upcoming features.', 'class' => 'infoicon']);
                    echo $this->Form->input('reqNew', 
                        array('value' => $current_metrics[2]['value'], 'label' => $metricNames[3],'type' => 'number', 'min' => 0, 'required' => true));
                    echo $this->Html->image('../webroot/img/infoicon.png', ['alt' => 'infoicon', 'title' => 'Sprint backlog is a list of features in progress.', 'class' => 'infoicon']);
                    echo $this->Form->input('reqInProgress', 
                        array('value' => $current_metrics[3]['value'], 'label' => $metricNames[4],'type' => 'number', 'min' => 0, 'required' => true));
                    echo $this->Form->input('reqClosed', 
                        array('value' => $current_metrics[4]['value'], 'label' => $metricNames[5],'type' => 'number', 'min' => 0, 'required' => true));
                    echo $this->Form->input('reqRejected', 
                        array('value' => $current_metrics[5]['value'], 'label' => $metricNames[6],'type' => 'number', 'min' => 0, 'required' => true));

                    echo $this->Html->image('../webroot/img/infoicon.png', ['alt' => 'infoicon', 'title' => 'Number of commits in master branch on your GitHub etc', 'class' => 'infoicon']);

                    if ($commitCount != null) {
                        echo $this->Form->input('commits', 
                            array('value' => $commitCount, 'label' => $metricNames[7],'type' => 'number', 'min' => 0, 'required' => true)); 
                    } else {
                        echo $this->Form->input('commits', 
                            array('value' => $current_metrics[6]['value'], 'label' => $metricNames[7],'type' => 'number', 'min' => 0, 'required' => true)); 
                    }

                    echo $this->Form->input('passedTestCases', 
                        array('value' => $current_metrics[7]['value'], 'label' => $metricNames[8],'type' => 'number', 'min' => 0, 'required' => true));

                    echo $this->Form->input('totalTestCases', 
                        array('value' => $current_metrics[8]['value'], 'label' => $metricNames[9],'type' => 'number', 'min' => 0, 'required' => true));
                ?>
            </div>
            <?php
                echo $this->Form->input('degreeReadiness', 
                    array('value' => $current_metrics[9]['value'], 'label' => $metricNames[10],'type' => 'number', 'min' => 0, 'max' => 100, 'required' => true));
                echo $this->Html->image('../webroot/img/infoicon.png', ['alt' => 'infoicon', 'title' => 'Estimate of how complete the project is (from 0 to 100 percent).', 'class' => 'infoicon']);
            ?>
            <div class="report-nav">
                <?= $this->Form->button('Next page', ['name' => 'submit', 'value' => 'next']);?>
                    <?= $this->Html->link('Previous page', ['name' => 'submit', 'value'=>'previous', 'controller' => 'Weeklyreports', 'action' => 'add'],['class' => 'link-button']); ?>
            </div>            
    </fieldset>
   
    <?= $this->Form->end() ?>
</div>
<style>
   .infoicon{float: right; margin-top: 31px; margin-left: 10px;}
</style>
