<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 *
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Product
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"upload", "Default"})
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"upload"})
     * @Assert\Image(
     *     groups={"upload"}
     * )
     * @Assert\File(
     *     maxSize = "1024k",
     *     groups={"upload"}
     * )
     * @ORM\Column(name="photo", type="string", length=255)
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity="Security", inversedBy="products")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Product
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return Product
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    public function getFullPhotoPath() {
        return null === $this->photo ? null : $this->getUploadRootDir(). $this->photo;
    }

    /**
     * the absolute directory path where uploaded documents should be saved
     *
     * @return string
     */
    protected function getUploadRootDir() {
        return $this->getTmpUploadRootDir().$this->getId()."/";
    }

    /**
     * the directory path where uploaded documents should be saved
     *
     * @return string
     */
    protected function getTmpUploadRootDir() {
        return __DIR__ . '/../../../web/upload/product/';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function uploadPhoto() {
        // the file property can be empty if the field is not required
        if (null === $this->photo || is_string($this->photo)) {
            return;
        }
        if(!$this->id){
            $this->photo->move($this->getTmpUploadRootDir(), $this->photo->getClientOriginalName());
        }else{
            $this->photo->move($this->getUploadRootDir(), $this->photo->getClientOriginalName());
        }
        $this->setPhoto($this->photo->getClientOriginalName());
    }

    /**
     * @ORM\PostPersist()
     */
    public function movePhoto()
    {
        if (null === $this->photo) {
            return;
        }
        if(!is_dir($this->getUploadRootDir())){
            mkdir($this->getUploadRootDir());
        }
        copy($this->getTmpUploadRootDir().$this->photo, $this->getFullPhotoPath());
        unlink($this->getTmpUploadRootDir().$this->photo);
    }

    /**
     * @ORM\PreRemove()
     */
    public function removePhoto()
    {
        if(file_exists($this->getFullPhotoPath())){
            unlink($this->getFullPhotoPath());
        }

        if(is_dir($this->getUploadRootDir())){
            rmdir($this->getUploadRootDir());
        }
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\Security $user
     * @return Product
     */
    public function setUser(\AppBundle\Entity\Security $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\Security 
     */
    public function getUser()
    {
        return $this->user;
    }
}
