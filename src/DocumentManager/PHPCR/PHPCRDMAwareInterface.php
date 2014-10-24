<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE.txt for copying permission.
 * ************************************************
 */

namespace Thorr\Persistence\Doctrine\DocumentManager\PHPCR;

use Doctrine\ODM\PHPCR\DocumentManager;

interface PHPCRDMAwareInterface
{
    /**
     * Set the document manager
     *
     * @param DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager);

    /**
     * Get the document manager
     *
     * @return DocumentManager
     */
    public function getDocumentManager();

}
