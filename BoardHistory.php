<?php
/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Entity;

use AppBundle\BackOffice\Entity\AbstractHistory;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BoardHistory.
 *
 * @ORM\Table(
 *     name="advent_board_history",
 *     indexes={
 *         @ORM\Index(name="logged_at", columns={"logged_at"}),
 *         @ORM\Index(name="object_id", columns={"object_id"})
 *     })
 *     @ORM\Entity(repositoryClass="AppBundle\AdventCalendar\Repository\BoardHistoryRepository")
 */
class BoardHistory extends AbstractHistory
{
}
