<?php
namespace Webit\UtilsBundle\Entity;

abstract class TranslatableEntity
{
    /**
     * getting names of the translatable columns
     */
    abstract public function getTranslatableColumns();

    /**
     * getting the class name of the translation entity
     */
    abstract public function getTranslationEntityName();

    /**
     * associated translation item with the parent item you have
     */
    abstract public function addTranslationItem($trans_item);

    public function getTranslationValues()
    {
        $trans_col = $this->getTranslations();
        $ret_arr = array();


        foreach ($trans_col as $trans) {

            $trans_obj_arr = array();
            $trans_obj_arr['lang'] = $trans->getLang();

            foreach ($this->getTranslatableColumns() as $field) {

                $trans_obj_arr[$field] = call_user_func(array($trans, 'get' . $this->camelize($field)));
            }
            $ret_arr[$trans->getLang()] = $trans_obj_arr;
        }

        return $ret_arr;
    }

    public function setTranslationValues(array $new_translations)
    {
        $old_trans_col = $this->getTranslations();
        $old_trans_col2 = array();
        foreach ($old_trans_col as $trans) {
            $old_trans_col2[$trans->getLang()] = $trans;
        }

        foreach ($new_translations as $new_trans) {

            if(is_array($new_trans)){
                $lang = $new_trans['lang'];
            }else{
                $lang = $new_trans->getLang();
            }

            if (isset($old_trans_col2[$lang])) {
                $new_trans_obj = $old_trans_col2[$lang];
            } else {
                $translation_model = $this->getTranslationEntityName();
                $new_trans_obj = new $translation_model();
                $new_trans_obj->setLang($lang);
            }

            $new_trans_obj->setTranslationParent($this);

            foreach ($this->getTranslatableColumns() as $col) {
                $value = null;
                if(is_array($new_trans)){ //don't know why its sometimes get array others get entity
                    $value = @$new_trans[$col];
                }else{
                    $getter = 'get'.$this->camelize($col);
                    $value = $new_trans->$getter();
                }
                call_user_func(array($new_trans_obj, 'set' . $this->camelize($col)), $value);
            }
            $this->addTranslationItem($new_trans_obj);
        }

    }

    public function camelize($string)
    {
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);

        return $string;
    }

    public function getTranslationByLang($lang)
    {
        $trans = $this->getTranslations();

        foreach($trans as $t){
            if($t->getLang()==$lang){
                return $t;
            }
        }
        return $trans[0];
    }

    public function __get($name)
    {
        if(in_array($name, $this->getTranslatableColumns())){
            $trans = $this->getTranslations();
            if(count($trans)){
                $main_trans = $trans[0];
                return call_user_method('get'.$this->camelize($name), $main_trans);
            }
        }

        return 'N.A';
    }
}
