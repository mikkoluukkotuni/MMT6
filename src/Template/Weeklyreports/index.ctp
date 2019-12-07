
<!--
        <ul class="side-nav">
            <li></li> 
        </ul>
-->
        
<div class="weeklyreports index large-9 medium-18 columns content float: left">
    <h3><?= __('Weekly reports') ?></h3>
    <?php
            $admin = $this->request->session()->read('is_admin');
            $supervisor = ( $this->request->session()->read('selected_project_role') == 'supervisor' ) ? 1 : 0;
            $manager = ( $this->request->session()->read('selected_project_role') == 'manager' ) ? 1 : 0;
            
            if($admin) { ?>
                <button id="managing_button"><?= $this->Html->link(__('Weekly hours'), ['controller' => 'Weeklyhours', 'action' => 'index']) ?></button>
            <?php }

            if($admin || $supervisor || $manager) {
        ?>
            <button id="navbutton"><?= $this->Html->link(__('+ New Report'), ['action' => 'add']) ?></button>
        <?php } ?>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th colspan="2"><?= __('Title') ?></th>
                <th style="text-align: center"><?= __('Week') ?></th>
                <th style="text-align: center"><?= __('Year') ?></th>
                <th><?= $this->Paginator->sort('created_on') ?></th>

                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($weeklyreports as $weeklyreport): ?>
            <tr>
                <td colspan="2"><?= h($weeklyreport->title) ?></td>
                <td style="text-align: center"><?= h($weeklyreport->week) ?></td>
                <td style="text-align: center"><?= h($weeklyreport->year) ?></td>		
                <td><?= h($weeklyreport->created_on->format('d.m.Y')) ?></td>
                <!--
                <td><?php 
                if ($weeklyreport->updated_on != NULL)
                    echo h($weeklyreport->updated_on->format('d.m.Y')); ?></td> -->
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $weeklyreport->id]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>
