<?php
/**
 * Copyright © 2016 Trive (http://www.trive.digital/) All rights reserved.
 */

namespace Trive\Revo\Controller\Adminhtml\Slider;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Trive\Revo\Controller\Adminhtml\Slider {
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){

        $resultRedirect = $this->_resultRedirectFactory->create();
        // Check if form data has been sent
        $sliderFormData = $this->getRequest()->getPostValue();
        if($sliderFormData){
            try{
                $slider_id = $this->getRequest()->getParam('slider_id');
                $productSlider = $this->_sliderFactory->create();
                if($slider_id !== null){
                    $productSlider->load($slider_id);
                }



                /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
	            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
	            ->getDirectoryRead(DirectoryList::MEDIA);
            	$path = $mediaDirectory->getAbsolutePath();
	
	            // Delete, Upload Image
                $imageFieldnames = ['background_image', 'background_image_mobile', 'title_background_image', 'title_background_image_mobile'];
                foreach($imageFieldnames as $imageFieldname){
                    $imagePath = "";
                    if( !empty($sliderFormData[$imageFieldname]['value']) ){
                        $imagePath = $path.$sliderFormData[$imageFieldname]['value'];
                    }
                    if(isset($sliderFormData[$imageFieldname]['delete']) && file_exists($imagePath)){
                        unlink($imagePath);
                        $sliderFormData[$imageFieldname] = '';
                    }
                    if(isset($sliderFormData[$imageFieldname]) && is_array($sliderFormData[$imageFieldname])){
                        unset($sliderFormData[$imageFieldname]);
                    }
                    if($image = $this->uploadImage($imageFieldname)){
                        $sliderFormData[$imageFieldname] = $image;
                    }
                }


                
                $productSlider->setData($sliderFormData);

                // Check for additional slider products
                if (isset($sliderFormData['slider_products']) && is_string($sliderFormData['slider_products']))
                {
                    $products = json_decode($sliderFormData['slider_products'], true);
                    $productSlider->setPostedProducts($products);
                    $productSlider->unsetData('slider_products');
                }

                // Save data
                $productSlider->save();

                if(!$slider_id){
                    $slider_id = $productSlider->getSliderId();
                }

                // Add success message
                $this->messageManager->addSuccess(__('Product slider has been successfully saved.'));
                // Clear previously saved data from session
                $this->_getSession()->setFormData(false);

                //Check if save is clicked or save and continue edit
                if($this->getRequest()->getParam('back') == 'edit'){
                    return $resultRedirect->setPath('*/*/edit', ['id' => $slider_id]);
                }

                //Go to grid
                return $resultRedirect->setPath('*/*/');

            } catch(\Exception $e){
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e,__('Error occurred during slider saving.'));
            }

            //Set entered form data so we don't have to enter it again (not saved in database)
            $this->_getSession()->setFormData($sliderFormData);
            // Return to edit
            return $resultRedirect->setPath('*/*/edit',['id' => $slider_id]);
        }
    }

	public function uploadImage($fieldId = 'image'){

        $resultRedirect = $this->resultRedirectFactory->create();

        if (isset($_FILES[$fieldId]) && $_FILES[$fieldId]['name']!=''){
            $_FILES[$fieldId]['name'] = $fieldId."_".$this->getRequest()->getParam('slider_id').".".pathinfo($_FILES[$fieldId]['name'], PATHINFO_EXTENSION);
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                array('fileId' => $fieldId)
            );

            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
			
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
            ->getDirectoryRead(DirectoryList::MEDIA);
            $mediaFolder = 'trive/revo/';
            try {
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); 
                //$uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $result = $uploader->save($mediaDirectory->getAbsolutePath($mediaFolder));
                return $mediaFolder.$result['file'];
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['slider_id' => $this->getRequest()->getParam('slider_id')]);
            }
        }
        return;
 	}
}
