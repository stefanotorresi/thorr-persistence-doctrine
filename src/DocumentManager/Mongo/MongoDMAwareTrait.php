<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DocumentManager\Mongo;

use Doctrine\ODM\MongoDB\DocumentManager;

trait MongoDMAwareTrait
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * Set the document manager
     *
     * @param  DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Get the document manager
     *
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }
}
