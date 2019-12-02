
<div class="notes view large-7 medium-16 columns content float: left">
    <h3><?= __('View feedback') ?></h3>
    <button id="navbutton">
        <?= $this->Form->postLink(
            __('Delete'),
            ['action' => 'delete', $note->id],
            ['confirm' => __('Are you sure you want to delete # {0}?', $note->id)]
        )?>
    </button>

    <table class="vertical-table">
        <tr>
            <th><?= __('Created on') ?></th>
            <td><?= h($note->created_on->format('d.m.Y')) ?></td>
        </tr>
        <tr>
            <th><?= __('Project role') ?></th>
            <td><?= h($note->project_role) ?></td>
        </tr>
        <tr>
            <th><?= __('Send a reply to user?') ?></th>
            <?php if ($note->contact_user == 1) {
                $reply = 'Yes';
            }
            else {
                $reply = 'No';
            } ?>
            <td><?= h($reply) ?></td>
        </tr>
         <tr>
            <th><?= __('Email') ?></th>
            <td><?= h($note->email) ?></td>
        </tr>               
        <tr>     
            <td colspan="2" style="text-align: left;"><?= h(wordwrap($note->content,40,"\n",TRUE)) ?></td>
        </tr>
    </table>
</div>
