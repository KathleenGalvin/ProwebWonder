<?php
/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Entity;

use AppBundle\BackOffice\Entity\AbstractHistory;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AttemptHistory.
 *
 * @ORM\Table(
 *     name="advent_attempt_history",
 *     indexes={
 *         @ORM\Index(name="logged_at", columns={"logged_at"}),
 *         @ORM\Index(name="object_id", columns={"object_id"})
 *     })
 *     @ORM\Entity(repositoryClass="AppBundle\AdventCalendar\Repository\AttemptHistoryRepository")
 */
class AttemptHistory extends AbstractHistory
{
}
