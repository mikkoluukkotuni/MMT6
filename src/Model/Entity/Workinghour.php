<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Workinghour Entity.
 *
 * @property int $id
 * @property int $member_id
 * @property \App\Model\Entity\Member $member
 * @property \Cake\I18n\Time $date
 * @property \Cake\I18n\Time $created_on
 * @property \Cake\I18n\Time $updated_on
 * @property string $description
 * @property float $duration
 * @property int $worktype
 */
class Workinghour extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
