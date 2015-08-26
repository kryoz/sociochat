<?php

namespace SocioChat\Message\Filters;

class LineBreakFilter implements ChainInterface
{
    const MAX_BR = 4;
	const BR = '<br>';

	/**
     * C-o-R pattern
     * @param Chain $chain input stream
     * @return false|null|true
     */
    public function handleRequest(Chain $chain)
    {
        $request = $chain->getRequest();
	    $msgParts = explode('|', $request['msg']);
	    $brCount = 1;

		if (!empty($msgParts)) {
			$newMsgParts = [];
			$brCount = min(self::MAX_BR, count($msgParts));

			for ($i = 0; $i < $brCount; $i++) {
				$part = trim($msgParts[$i]);
				if (!$part) {
					continue;
				}
				$newMsgParts[] = $part;
			}

			$request['msg'] = implode(self::BR, $newMsgParts);
		}

	    $props = $chain->getUser()->getProperties();
	    $props->setMessagesCount($props->getMessagesCount() + $brCount);

        $chain->setRequest($request);
    }
}
