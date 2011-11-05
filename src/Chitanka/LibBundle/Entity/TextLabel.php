<?php

namespace Chitanka\LibBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** FIXME doctrine:schema:create does not allow this entity table
* @ORM\Entity
* @ORM\Table(name="text_label",
*	indexes={
*		@ORM\Index(name="label_idx", columns={"label_id"})})
*/
class TextLabel
{
	/**
	* @var integer
	* @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;

	/**
	* @var integer
	* @ORM\Id
	* @ORM\ManyToOne(targetEntity="Text", inversedBy="textLabels")
	*/
	private $text;

	/**
	* @var integer
	* @ORM\Id
	* @ORM\ManyToOne(targetEntity="Label")
	*/
	private $label;

	public function getId() { return $this->id; }

	public function getText() { return $this->text; }
	public function setText($text) { $this->text = $text; }

	public function getLabel() { return $this->label; }
	public function setLabel($label) { $this->label = $label; }
}
