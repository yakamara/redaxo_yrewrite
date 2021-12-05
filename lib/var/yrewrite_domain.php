<?php

/**
 * REX_YREWRITE_DOMAIN[field="id"]
 */
class rex_var_yrewrite_domain extends rex_var
{
    protected function getOutput()
    {
        if (!in_array($this->getContext(), ['module', 'action', 'template'])) { // || !is_numeric($id) || $id < 1 || $id > 20
            return false;
        }

        $value = '';
        switch ($this->getArg('field', '', true)) {
            case 'id':
                $value = 'rex_yrewrite::getCurrentDomain()->getId()';
                break;
            case 'mount_id':
                $value = 'rex_yrewrite::getCurrentDomain()->getMountId()';
                break;
            case 'name':
                $value = 'rex_yrewrite::getCurrentDomain()->getName()';
                break;
            case 'host':
                $value = 'rex_yrewrite::getCurrentDomain()->getHost()';
                break;
            case 'start_id':
                $value = 'rex_yrewrite::getCurrentDomain()->getStartId()';
                break;
            case 'not_found_id':
                $value = 'rex_yrewrite::getCurrentDomain()->getNotfoundId()';
                break;
            case 'clang':
                $value = 'rex_yrewrite::getCurrentDomain()->getStartClang()';
                break;
            case 'url':
                $value = 'rex_yrewrite::getCurrentDomain()->getUrl()';
                break;
            case 'path':
                $value = 'rex_yrewrite::getCurrentDomain()->getPath()';
                break;
            case 'robots':
                $value = 'rex_yrewrite::getCurrentDomain()->getRobots()';
                break;
        }

        return $value;
    }
}
