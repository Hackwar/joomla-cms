<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * Copyright (c) 2009 Vassilis Spiliopoulos (http://www.psychfamily.gr),
 * Pantelis Nasikas under GNU General Public License Version 3
 * Updated for Drupal 6, 7 and Drupal 8 by
 * Yannis Karampelas (info@netstudio.gr) in 2011 and 2017 respectively.
 * This is a port of the php implementation of
 * Spyros Saroukos into Drupal CMS. Spyros Saroukos implementation
 * was based on the work of Panos Kyriakakis (http://www.salix.gr) and
 * Georgios Ntais (Georgios.Ntais@eurodyn.com)
 * Georgios firstly developed the stemmer's javascript implementation for his
 * master thesis at Royal Institute of Technology [KTH], Stockholm Sweden
 * http://www.dsv.su.se/~hercules/papers/Ntais_greek_stemmer_thesis_final.pdf
 *
 * !!!!!The encoding of this file is iso-8859-7 instead of UTF-8 on purpose!!!!!!!
 */

defined('_JEXEC') or die;

/**
 * Greek language support class for the Finder indexer package.
 *
 * @since  __DEPLOY_VERSION__
 */
class FinderIndexerLanguageel_GR extends FinderIndexerLanguage
{
	/**
	 * Method to stem a token.
	 *
	 * @param   string  $token  The token to stem.
	 *
	 * @return  string  The stemmed token.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function stem($word)
	{
		$w = $word;
		$numberOfRulesExamined = 0; //this is the number of rules examined. for deubugging and testing purposes

		// it is better to convert the input into iso-8859-7 in case it is in utf-8
		// this way we dont have any problems with length counting etc
		$encoding_changed= FALSE;
		$w_CASE=array(strlen($w));//1 for changed case in that position, 2 especially for Ï‚

		//first we must find all letters that are not in Upper case and store their position
		$unacceptedLetters=array("Î±","Î²","Î³","Î´","Îµ","Î¶","Î·","Î¸","Î¹","Îº","Î»","Î¼","Î½","Î¾","Î¿","Ï€","Ï","Ïƒ","Ï„","Ï…","Ï†","Ï‡","Ïˆ","Ï‰","Î¬","Î­","Î®","Î¯","ÏŒ","Ï","Ï‚","ÏŽ","ÏŠ");
		$acceptedLetters=array("Î‘","Î’","Î“","Î”","Î•","Î–","Î—","Î˜","Î™","Îš","Î›","Îœ","Î","Îž","ÎŸ","Î ","Î¡","Î£","Î¤","Î¥","Î¦","Î§","Î¨","Î©","Î‘","Î•","Î—","Î™","ÎŸ","Î¥","Î£","Î©","Î™");

		for($k=0;$k<=32;$k=$k+1){
			for ($i=0;$i<=strlen($w)-1;$i++){
				if($w[$i]==$unacceptedLetters[$k])
				{
					if ($w[$i]=="Ï‚"){$w[$i]="Î£";$w_CASE[$i]=2;}
					else{$w[$i]=$acceptedLetters[$k];
						$w_CASE[$i]="1";}
				}
			}
		}
		//stop-word removal
		$numberOfRulesExamined++;
		$stop_words='/^(Î•ÎšÎŸ|Î‘Î’Î‘|Î‘Î“Î‘|Î‘Î“Î—|Î‘Î“Î©|Î‘Î”Î—|Î‘Î”Î©|Î‘Î•|Î‘Î•Î™|Î‘Î˜Î©|Î‘Î™|Î‘Î™Îš|Î‘ÎšÎ—|Î‘ÎšÎŸÎœÎ‘|Î‘ÎšÎŸÎœÎ—|Î‘ÎšÎ¡Î™Î’Î©Î£|Î‘Î›Î‘|Î‘Î›Î—Î˜Î•Î™Î‘|Î‘Î›Î—Î˜Î™ÎÎ‘|Î‘Î›Î›Î‘Î§ÎŸÎ¥|Î‘Î›Î›Î™Î©Î£|Î‘Î›Î›Î™Î©Î¤Î™ÎšÎ‘|Î‘Î›Î›ÎŸÎ™Î©Î£|Î‘Î›Î›ÎŸÎ™Î©Î¤Î™ÎšÎ‘|Î‘Î›Î›ÎŸÎ¤Î•|Î‘Î›Î¤|Î‘Î›Î©|Î‘ÎœÎ‘|Î‘ÎœÎ•|Î‘ÎœÎ•Î£Î‘|Î‘ÎœÎ•Î£Î©Î£|Î‘ÎœÎ©|Î‘Î|Î‘ÎÎ‘|Î‘ÎÎ‘ÎœÎ•Î£Î‘|Î‘ÎÎ‘ÎœÎ•Î¤Î‘ÎžÎ¥|Î‘ÎÎ•Î¥|Î‘ÎÎ¤Î™|Î‘ÎÎ¤Î™Î Î•Î¡Î‘|Î‘ÎÎ¤Î™Î£|Î‘ÎÎ©|Î‘ÎÎ©Î¤Î•Î¡Î©|Î‘ÎžÎ‘Î¦ÎÎ‘|Î‘Î |Î‘Î Î•ÎÎ‘ÎÎ¤Î™|Î‘Î ÎŸ|Î‘Î ÎŸÎ¨Î•|Î‘Î Î©|Î‘Î¡Î‘|Î‘Î¡Î‘Î“Î•|Î‘Î¡Î•|Î‘Î¡Îš|Î‘Î¡ÎšÎ•Î¤Î‘|Î‘Î¡Î›|Î‘Î¡Îœ|Î‘Î¡Î¤|Î‘Î¡Î¥|Î‘Î¡Î©|Î‘Î£|Î‘Î£Î‘|Î‘Î£ÎŸ|Î‘Î¤Î‘|Î‘Î¤Î•|Î‘Î¤Î—|Î‘Î¤Î™|Î‘Î¤Îœ|Î‘Î¤ÎŸ|Î‘Î¥Î¡Î™ÎŸ|Î‘Î¦Î—|Î‘Î¦ÎŸÎ¤ÎŸÎ¥|Î‘Î¦ÎŸÎ¥|Î‘Î§|Î‘Î§Î•|Î‘Î§ÎŸ|Î‘Î¨Î‘|Î‘Î¨Î•|Î‘Î¨Î—|Î‘Î¨Î¥|Î‘Î©Î•|Î‘Î©ÎŸ|Î’Î‘Î|Î’Î‘Î¤|Î’Î‘Î§|Î’Î•Î‘|Î’Î•Î’Î‘Î™ÎŸÎ¤Î‘Î¤Î‘|Î’Î—Îž|Î’Î™Î‘|Î’Î™Î•|Î’Î™Î—|Î’Î™ÎŸ|Î’ÎŸÎ—|Î’ÎŸÎ©|Î’Î¡Î•|Î“Î‘|Î“Î‘Î’|Î“Î‘Î¡|Î“Î•Î|Î“Î•Î£||Î“Î—|Î“Î—Î|Î“Î™|Î“Î™Î‘|Î“Î™Î•|Î“Î™Î|Î“Î™ÎŸ|Î“ÎšÎ™|Î“Î™Î‘Î¤Î™|Î“ÎšÎ¥|Î“ÎŸÎ—|Î“ÎŸÎŸ|Î“Î¡Î—Î“ÎŸÎ¡Î‘|Î“Î¡Î™|Î“Î¡Î¥|Î“Î¥Î—|Î“Î¥Î¡Î©|Î”Î‘|Î”Î•|Î”Î•Î—|Î”Î•Î™|Î”Î•Î|Î”Î•Î£|Î”Î—|Î”Î—Î˜Î•Î|Î”Î—Î›Î‘Î”Î—|Î”Î—Î©|Î”Î™|Î”Î™Î‘|Î”Î™Î‘Î¡ÎšÎ©Î£|Î”Î™ÎŸÎ›ÎŸÎ¥|Î”Î™Î£|Î”Î™Î§Î©Î£|Î”ÎŸÎ›|Î”ÎŸÎ|Î”Î¡Î‘|Î”Î¡Î¥|Î”Î¡Î§|Î”Î¥Î•|Î”Î¥ÎŸ|Î”Î©|Î•Î‘Îœ|Î•Î‘Î|Î•Î‘Î¡|Î•Î˜Î—|Î•Î™|Î•Î™Î”Î•ÎœÎ—|Î•Î™Î˜Î•|Î•Î™ÎœÎ‘Î™|Î•Î™ÎœÎ‘Î£Î¤Î•|Î•Î™ÎÎ‘Î™|Î•Î™Î£|Î•Î™Î£Î‘Î™|Î•Î™Î£Î‘Î£Î¤Î•|Î•Î™Î£Î¤Î•|Î•Î™Î¤Î•|Î•Î™Î§Î‘|Î•Î™Î§Î‘ÎœÎ•|Î•Î™Î§Î‘Î|Î•Î™Î§Î‘Î¤Î•|Î•Î™Î§Î•|Î•Î™Î§Î•Î£|Î•Îš|Î•ÎšÎ•Î™|Î•Î›Î‘|Î•Î›Î™|Î•ÎœÎ |Î•Î|Î•ÎÎ¤Î•Î›Î©Î£|Î•ÎÎ¤ÎŸÎ£|Î•ÎÎ¤Î©ÎœÎ•Î¤Î‘ÎžÎ¥|Î•ÎÎ©|Î•Îž|Î•ÎžÎ‘Î¦ÎÎ‘|Î•ÎžÎ™|Î•ÎžÎ™Î£ÎŸÎ¥|Î•ÎžÎ©|Î•ÎŸÎš|Î•Î Î‘ÎÎ©|Î•Î Î•Î™Î”Î—|Î•Î Î•Î™Î¤Î‘|Î•Î Î—|Î•Î Î™|Î•Î Î™Î£Î—Î£|Î•Î ÎŸÎœÎ•ÎÎ©Î£|Î•Î¡Î‘|Î•Î£|Î•Î£Î‘Î£|Î•Î£Î•|Î•Î£Î•Î™Î£|Î•Î£Î•ÎÎ‘|Î•Î£Î—|Î•Î£Î¤Î©|Î•Î£Î¥|Î•Î£Î©|Î•Î¤Î™|Î•Î¤Î£Î™|Î•Î¥|Î•Î¥Î‘|Î•Î¥Î“Î•|Î•Î¥Î˜Î¥Î£|Î•Î¥Î¤Î¥Î§Î©Î£|Î•Î¦Î•|Î•Î¦Î•ÎžÎ—Î£|Î•Î¦Î¤|Î•Î§Î•|Î•Î§Î•Î™|Î•Î§Î•Î™Î£|Î•Î§Î•Î¤Î•|Î•Î§Î˜Î•Î£|Î•Î§ÎŸÎœÎ•|Î•Î§ÎŸÎ¥ÎœÎ•|Î•Î§ÎŸÎ¥Î|Î•Î§Î¤Î•Î£|Î•Î§Î©|Î•Î©Î£|Î–Î•Î‘|Î–Î•Î—|Î–Î•Î™|Î–Î•Î|Î–Î—Î|Î–Î©|Î—|Î—Î”Î—|Î—Î”Î¥|Î—Î˜Î—|Î—Î›ÎŸ|Î—ÎœÎ™|Î—Î Î‘|Î—Î£Î‘Î£Î¤Î•|Î—Î£ÎŸÎ¥Î|Î—Î¤Î‘|Î—Î¤Î‘Î|Î—Î¤Î‘ÎÎ•|Î—Î¤ÎŸÎ™|Î—Î¤Î¤ÎŸÎ|Î—Î©|Î˜Î‘|Î˜Î¥Î•|Î˜Î©Î¡|Î™|Î™Î‘|Î™Î’ÎŸ|Î™Î”Î—|Î™Î”Î™Î©Î£|Î™Î•|Î™Î™|Î™Î™Î™|Î™ÎšÎ‘|Î™Î›ÎŸ|Î™ÎœÎ‘|Î™ÎÎ‘|Î™ÎÎ©|Î™ÎžÎ•|Î™ÎžÎŸ|Î™ÎŸ|Î™ÎŸÎ™|Î™Î£Î‘|Î™Î£Î‘ÎœÎ•|Î™Î£Î•|Î™Î£Î—|Î™Î£Î™Î‘|Î™Î£ÎŸ|Î™Î£Î©Î£|Î™Î©Î’|Î™Î©Î|Î™Î©Î£|Î™Î‘Î|ÎšÎ‘Î˜|ÎšÎ‘Î˜Î•|ÎšÎ‘Î˜Î•Î¤Î™|ÎšÎ‘Î˜ÎŸÎ›ÎŸÎ¥|ÎšÎ‘Î˜Î©Î£|ÎšÎ‘Î™|ÎšÎ‘Î|ÎšÎ‘Î ÎŸÎ¤Î•|ÎšÎ‘Î ÎŸÎ¥|ÎšÎ‘Î Î©Î£|ÎšÎ‘Î¤|ÎšÎ‘Î¤Î‘|ÎšÎ‘Î¤Î™|ÎšÎ‘Î¤Î™Î¤Î™|ÎšÎ‘Î¤ÎŸÎ Î™Î|ÎšÎ‘Î¤Î©|ÎšÎ‘Î©|ÎšÎ’ÎŸ|ÎšÎ•Î‘|ÎšÎ•Î™|ÎšÎ•Î|ÎšÎ™|ÎšÎ™Îœ|ÎšÎ™ÎŸÎ›Î‘Î£|ÎšÎ™Î¤|ÎšÎ™Î§|ÎšÎšÎ•|ÎšÎ›Î™Î£Î•|ÎšÎ›Î |ÎšÎŸÎš|ÎšÎŸÎÎ¤Î‘|ÎšÎŸÎ§|ÎšÎ¤Î›|ÎšÎ¥Î¡|ÎšÎ¥Î¡Î™Î©Î£|ÎšÎ©|ÎšÎ©Î|Î›Î‘|Î›Î•Î‘|Î›Î•Î|Î›Î•ÎŸ|Î›Î™Î‘|Î›Î™Î“Î‘ÎšÎ™|Î›Î™Î“ÎŸÎ¥Î›Î‘ÎšÎ™|Î›Î™Î“ÎŸ|Î›Î™Î“Î©Î¤Î•Î¡ÎŸ|Î›Î™ÎŸ|Î›Î™Î¡|Î›ÎŸÎ“Î©|Î›ÎŸÎ™Î Î‘|Î›ÎŸÎ™Î ÎŸÎ|Î›ÎŸÎ£|Î›Î£|Î›Î¥Î©|ÎœÎ‘|ÎœÎ‘Î–Î™|ÎœÎ‘ÎšÎ‘Î¡Î™|ÎœÎ‘Î›Î™Î£Î¤Î‘|ÎœÎ‘Î›Î›ÎŸÎ|ÎœÎ‘Î|ÎœÎ‘Îž|ÎœÎ‘Î£|ÎœÎ‘Î¤|ÎœÎ•|ÎœÎ•Î˜Î‘Î¥Î¡Î™ÎŸ|ÎœÎ•Î™|ÎœÎ•Î™ÎŸÎ|ÎœÎ•Î›|ÎœÎ•Î›Î•Î™|ÎœÎ•Î›Î›Î•Î¤Î‘Î™|ÎœÎ•ÎœÎ™Î‘Î£|ÎœÎ•Î|ÎœÎ•Î£|ÎœÎ•Î£Î‘|ÎœÎ•Î¤|ÎœÎ•Î¤Î‘|ÎœÎ•Î¤Î‘ÎžÎ¥|ÎœÎ•Î§Î¡Î™|ÎœÎ—|ÎœÎ—Î”Î•|ÎœÎ—Î|ÎœÎ—Î Î©Î£|ÎœÎ—Î¤Î•|ÎœÎ™|ÎœÎ™Îž|ÎœÎ™Î£|ÎœÎœÎ•|ÎœÎÎ‘|ÎœÎŸÎ’|ÎœÎŸÎ›Î™Î£|ÎœÎŸÎ›ÎŸÎÎŸÎ¤Î™|ÎœÎŸÎÎ‘Î§Î‘|ÎœÎŸÎÎŸÎœÎ™Î‘Î£|ÎœÎ™Î‘|ÎœÎŸÎ¥|ÎœÎ Î‘|ÎœÎ ÎŸÎ¡Î•Î™|ÎœÎ ÎŸÎ¡ÎŸÎ¥Î|ÎœÎ Î¡Î‘Î’ÎŸ|ÎœÎ Î¡ÎŸÎ£|ÎœÎ Î©|ÎœÎ¥|ÎœÎ¥Î‘|ÎœÎ¥Î|ÎÎ‘|ÎÎ‘Î•|ÎÎ‘Î™|ÎÎ‘ÎŸ|ÎÎ”|ÎÎ•Î|ÎÎ•Î‘|ÎÎ•Î•|ÎÎ•ÎŸ|ÎÎ™|ÎÎ™Î‘|ÎÎ™Îš|ÎÎ™Î›|ÎÎ™Î|ÎÎ™ÎŸ|ÎÎ¤Î‘|ÎÎ¤Î•|ÎÎ¤Î™|ÎÎ¤ÎŸ|ÎÎ¥Î|ÎÎ©Î•|ÎÎ©Î¡Î™Î£|ÎžÎ‘ÎÎ‘|ÎžÎ‘Î¦ÎÎ™ÎšÎ‘|ÎžÎ•Î©|ÎžÎ™|ÎŸ|ÎŸÎ‘|ÎŸÎ‘Î |ÎŸÎ”ÎŸ|ÎŸÎ•|ÎŸÎ–ÎŸ|ÎŸÎ—Î•|ÎŸÎ™|ÎŸÎ™Î‘|ÎŸÎ™Î—|ÎŸÎšÎ‘|ÎŸÎ›ÎŸÎ“Î¥Î¡Î‘|ÎŸÎ›ÎŸÎÎ•Î|ÎŸÎ›ÎŸÎ¤Î•Î›Î‘|ÎŸÎ›Î©Î£Î”Î™ÎŸÎ›ÎŸÎ¥|ÎŸÎœÎ©Î£|ÎŸÎ|ÎŸÎÎ•|ÎŸÎÎŸ|ÎŸÎ Î‘|ÎŸÎ Î•|ÎŸÎ Î—|ÎŸÎ ÎŸ|ÎŸÎ ÎŸÎ™Î‘Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™Î‘ÎÎ”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™Î‘Î£Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™Î•Î£Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™ÎŸÎ”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™ÎŸÎÎ”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™ÎŸÎ£Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™ÎŸÎ¥Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™ÎŸÎ¥Î£Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ™Î©ÎÎ”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ¤Î•Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ ÎŸÎ¥|ÎŸÎ ÎŸÎ¥Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ Î©Î£|ÎŸÎ¡Î‘|ÎŸÎ¡Î•|ÎŸÎ¡Î—|ÎŸÎ¡ÎŸ|ÎŸÎ¡Î¦|ÎŸÎ¡Î©|ÎŸÎ£Î‘|ÎŸÎ£Î‘Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ£Î•|ÎŸÎ£Î•Î£Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ£Î—Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ£Î—ÎÎ”Î—Î ÎŸÎ¤Î•|ÎŸÎ£Î—Î£Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ£ÎŸÎ”Î—Î ÎŸÎ¤Î•|ÎŸÎ£ÎŸÎ™Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ£ÎŸÎÎ”Î—Î ÎŸÎ¤Î•|ÎŸÎ£ÎŸÎ£Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ£ÎŸÎ¥Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ£ÎŸÎ¥Î£Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ£Î©ÎÎ”Î—Î ÎŸÎ¤Î•|ÎŸÎ¤Î‘Î|ÎŸÎ¤Î•|ÎŸÎ¤Î™|ÎŸÎ¤Î™Î”Î—Î ÎŸÎ¤Î•|ÎŸÎ¥|ÎŸÎ¥Î”Î•|ÎŸÎ¥Îš|ÎŸÎ¥Î£|ÎŸÎ¥Î¤Î•|ÎŸÎ¥Î¦|ÎŸÎ§Î™|ÎŸÎ¨Î‘|ÎŸÎ¨Î•|ÎŸÎ¨Î—|ÎŸÎ¨Î™|ÎŸÎ¨ÎŸ|Î Î‘|Î Î‘Î›Î™|Î Î‘Î|Î Î‘ÎÎ¤ÎŸÎ¤Î•|Î Î‘ÎÎ¤ÎŸÎ¥|Î Î‘ÎÎ¤Î©Î£|Î Î‘Î |Î Î‘Î¡|Î Î‘Î¡Î‘|Î Î•Î™|Î Î•Î¡|Î Î•Î¡Î‘|Î Î•Î¡Î™|Î Î•Î¡Î™Î ÎŸÎ¥|Î Î•Î¡Î£Î™|Î Î•Î¡Î¥Î£Î™|Î Î•Î£|Î Î™|Î Î™Î‘|Î Î™Î˜Î‘ÎÎŸÎ|Î Î™Îš|Î Î™ÎŸ|Î Î™Î£Î©|Î Î™Î¤|Î Î™Î©|Î Î›Î‘Î™|Î Î›Î•ÎŸÎ|Î Î›Î—Î|Î Î›Î©|Î Îœ|Î ÎŸÎ‘|Î ÎŸÎ•|Î ÎŸÎ›|Î ÎŸÎ›Î¥|Î ÎŸÎ |Î ÎŸÎ¤Î•|Î ÎŸÎ¥|Î ÎŸÎ¥Î˜Î•|Î ÎŸÎ¥Î˜Î•ÎÎ‘|Î Î¡Î•Î Î•Î™|Î Î¡Î™|Î Î¡Î™Î|Î Î¡ÎŸ|Î Î¡ÎŸÎšÎ•Î™ÎœÎ•ÎÎŸÎ¥|Î Î¡ÎŸÎšÎ•Î™Î¤Î‘Î™|Î Î¡ÎŸÎ Î•Î¡Î£Î™|Î Î¡ÎŸÎ£|Î Î¡ÎŸÎ¤ÎŸÎ¥|Î Î¡ÎŸÎ§Î˜Î•Î£|Î Î¡ÎŸÎ§Î¤Î•Î£|Î Î¡Î©Î¤Î¥Î¤Î•Î¡Î‘|Î Î¥Î‘|Î Î¥Îž|Î Î¥ÎŸ|Î Î¥Î¡|Î Î§|Î Î©|Î Î©Î›|Î Î©Î£|Î¡Î‘|Î¡Î‘Î™|Î¡Î‘Î |Î¡Î‘Î£|Î¡Î•|Î¡Î•Î‘|Î¡Î•Î•|Î¡Î•Î™|Î¡Î—Î£|Î¡Î˜Î©|Î¡Î™ÎŸ|Î¡ÎŸ|Î¡ÎŸÎ|Î¡ÎŸÎ•|Î¡ÎŸÎ–|Î¡ÎŸÎ—|Î¡ÎŸÎ˜|Î¡ÎŸÎ™|Î¡ÎŸÎš|Î¡ÎŸÎ›|Î¡ÎŸÎ|Î¡ÎŸÎ£|Î¡ÎŸÎ¥|Î£Î‘Î™|Î£Î‘Î|Î£Î‘ÎŸ|Î£Î‘Î£|Î£Î•|Î£Î•Î™Î£|Î£Î•Îš|Î£Î•Îž|Î£Î•Î¡|Î£Î•Î¤|Î£Î•Î¦|Î£Î—ÎœÎ•Î¡Î‘|Î£Î™|Î£Î™Î‘|Î£Î™Î“Î‘|Î£Î™Îš|Î£Î™Î§|Î£ÎšÎ™|Î£ÎŸÎ™|Î£ÎŸÎš|Î£ÎŸÎ›|Î£ÎŸÎ|Î£ÎŸÎ£|Î£ÎŸÎ¥|Î£Î¡Î™|Î£Î¤Î‘|Î£Î¤Î—|Î£Î¤Î—Î|Î£Î¤Î—Î£|Î£Î¤Î™Î£|Î£Î¤ÎŸ|Î£Î¤ÎŸÎ|Î£Î¤ÎŸÎ¥|Î£Î¤ÎŸÎ¥Î£|Î£Î¤Î©Î|Î£Î¥|Î£Î¥Î“Î§Î¡ÎŸÎÎ©Î£|Î£Î¥Î|Î£Î¥ÎÎ‘ÎœÎ‘|Î£Î¥ÎÎ•Î Î©Î£|Î£Î¥ÎÎ—Î˜Î©Î£|Î£Î§Î•Î”ÎŸÎ|Î£Î©Î£Î¤Î‘|Î¤Î‘|Î¤Î‘Î”Î•|Î¤Î‘Îš|Î¤Î‘Î|Î¤Î‘ÎŸ|Î¤Î‘Î¥|Î¤Î‘Î§Î‘|Î¤Î‘Î§Î‘Î¤Î•|Î¤Î•|Î¤Î•Î™|Î¤Î•Î›|Î¤Î•Î›Î™ÎšÎ‘|Î¤Î•Î›Î™ÎšÎ©Î£|Î¤Î•Î£|Î¤Î•Î¤|Î¤Î–ÎŸ|Î¤Î—|Î¤Î—Î›|Î¤Î—Î|Î¤Î—Î£|Î¤Î™|Î¤Î™Îš|Î¤Î™Îœ|Î¤Î™Î ÎŸÎ¤Î‘|Î¤Î™Î ÎŸÎ¤Î•|Î¤Î™Î£|Î¤ÎÎ¤|Î¤ÎŸ|Î¤ÎŸÎ™|Î¤ÎŸÎš|Î¤ÎŸÎœ|Î¤ÎŸÎ|Î¤ÎŸÎ |Î¤ÎŸÎ£|Î¤ÎŸÎ£?Î|Î¤ÎŸÎ£Î‘|Î¤ÎŸÎ£Î•Î£|Î¤ÎŸÎ£Î—|Î¤ÎŸÎ£Î—Î|Î¤ÎŸÎ£Î—Î£|Î¤ÎŸÎ£ÎŸ|Î¤ÎŸÎ£ÎŸÎ™|Î¤ÎŸÎ£ÎŸÎ|Î¤ÎŸÎ£ÎŸÎ£|Î¤ÎŸÎ£ÎŸÎ¥|Î¤ÎŸÎ£ÎŸÎ¥Î£|Î¤ÎŸÎ¤Î•|Î¤ÎŸÎ¥|Î¤ÎŸÎ¥Î›Î‘Î§Î™Î£Î¤ÎŸ|Î¤ÎŸÎ¥Î›Î‘Î§Î™Î£Î¤ÎŸÎ|Î¤ÎŸÎ¥Î£|Î¤Î£|Î¤Î£Î‘|Î¤Î£Î•|Î¤Î¥Î§ÎŸÎ|Î¤Î©|Î¤Î©Î|Î¤Î©Î¡Î‘|Î¥Î‘Î£|Î¥Î’Î‘|Î¥Î’ÎŸ|Î¥Î™Î•|Î¥Î™ÎŸ|Î¥Î›Î‘|Î¥Î›Î—|Î¥ÎÎ™|Î¥Î |Î¥Î Î•Î¡|Î¥Î ÎŸ|Î¥Î ÎŸÎ¨Î—|Î¥Î ÎŸÎ¨Î™Î|Î¥Î£Î¤Î•Î¡Î‘|Î¥Î¦Î—|Î¥Î¨Î—|Î¦Î‘|Î¦Î‘Î|Î¦Î‘Î•|Î¦Î‘Î|Î¦Î‘Îž|Î¦Î‘Î£|Î¦Î‘Î©|Î¦Î•Î–|Î¦Î•Î™|Î¦Î•Î¤ÎŸÎ£|Î¦Î•Î¥|Î¦Î™|Î¦Î™Î›|Î¦Î™Î£|Î¦ÎŸÎž|Î¦Î Î‘|Î¦Î¡Î™|Î§Î‘|Î§Î‘Î—|Î§Î‘Î›|Î§Î‘Î|Î§Î‘Î¦|Î§Î•|Î§Î•Î™|Î§Î˜Î•Î£|Î§Î™|Î§Î™Î‘|Î§Î™Î›|Î§Î™ÎŸ|Î§Î›Îœ|Î§Îœ|Î§ÎŸÎ—|Î§ÎŸÎ›|Î§Î¡Î©|Î§Î¤Î•Î£|Î§Î©Î¡Î™Î£|Î§Î©Î¡Î™Î£Î¤Î‘|Î¨Î•Î£|Î¨Î—Î›Î‘|Î¨Î™|Î¨Î™Î¤|Î©|Î©Î‘|Î©Î‘Î£|Î©Î”Î•|Î©Î•Î£|Î©Î˜Î©|Î©ÎœÎ‘|Î©ÎœÎ•|Î©Î|Î©ÎŸ|Î©ÎŸÎ|Î©ÎŸÎ¥|Î©Î£|Î©Î£Î‘Î|Î©Î£Î—|Î©Î£ÎŸÎ¤ÎŸÎ¥|Î©Î£Î ÎŸÎ¥|Î©Î£Î¤Î•|Î©Î£Î¤ÎŸÎ£ÎŸ|Î©Î¤Î‘|Î©Î§|Î©Î©Î)$/';

		if (preg_match($stop_words,$w)){
			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}

		// step1list is used in Step 1. 41 stems
		$step1list =  Array();
		$step1list["Î¦Î‘Î“Î™Î‘"]="Î¦Î‘";
		$step1list["Î¦Î‘Î“Î™ÎŸÎ¥"]="Î¦Î‘";
		$step1list["Î¦Î‘Î“Î™Î©Î"]="Î¦Î‘";
		$step1list["Î£ÎšÎ‘Î“Î™Î‘"]="Î£ÎšÎ‘";
		$step1list["Î£ÎšÎ‘Î“Î™ÎŸÎ¥"]="Î£ÎšÎ‘";
		$step1list["Î£ÎšÎ‘Î“Î™Î©Î"]="Î£ÎšÎ‘";
		$step1list["ÎŸÎ›ÎŸÎ“Î™ÎŸÎ¥"]="ÎŸÎ›ÎŸ";
		$step1list["ÎŸÎ›ÎŸÎ“Î™Î‘"]="ÎŸÎ›ÎŸ";
		$step1list["ÎŸÎ›ÎŸÎ“Î™Î©Î"]="ÎŸÎ›ÎŸ";
		$step1list["Î£ÎŸÎ“Î™ÎŸÎ¥"]="Î£ÎŸ";
		$step1list["Î£ÎŸÎ“Î™Î‘"]="Î£ÎŸ";
		$step1list["Î£ÎŸÎ“Î™Î©Î"]="Î£ÎŸ";
		$step1list["Î¤Î‘Î¤ÎŸÎ“Î™Î‘"]="Î¤Î‘Î¤ÎŸ";
		$step1list["Î¤Î‘Î¤ÎŸÎ“Î™ÎŸÎ¥"]="Î¤Î‘Î¤ÎŸ";
		$step1list["Î¤Î‘Î¤ÎŸÎ“Î™Î©Î"]="Î¤Î‘Î¤ÎŸ";
		$step1list["ÎšÎ¡Î•Î‘Î£"]="ÎšÎ¡Î•";
		$step1list["ÎšÎ¡Î•Î‘Î¤ÎŸÎ£"]="ÎšÎ¡Î•";
		$step1list["ÎšÎ¡Î•Î‘Î¤Î‘"]="ÎšÎ¡Î•";
		$step1list["ÎšÎ¡Î•Î‘Î¤Î©Î"]="ÎšÎ¡Î•";
		$step1list["Î Î•Î¡Î‘Î£"]="Î Î•Î¡";
		$step1list["Î Î•Î¡Î‘Î¤ÎŸÎ£"]="Î Î•Î¡";
		$step1list["Î Î•Î¡Î‘Î¤Î—"]="Î Î•Î¡"; //Added by Spyros . also at $re in step1
		$step1list["Î Î•Î¡Î‘Î¤Î‘"]="Î Î•Î¡";
		$step1list["Î Î•Î¡Î‘Î¤Î©Î"]="Î Î•Î¡";
		$step1list["Î¤Î•Î¡Î‘Î£"]="Î¤Î•Î¡";
		$step1list["Î¤Î•Î¡Î‘Î¤ÎŸÎ£"]="Î¤Î•Î¡";
		$step1list["Î¤Î•Î¡Î‘Î¤Î‘"]="Î¤Î•Î¡";
		$step1list["Î¤Î•Î¡Î‘Î¤Î©Î"]="Î¤Î•Î¡";
		$step1list["Î¦Î©Î£"]="Î¦Î©";
		$step1list["Î¦Î©Î¤ÎŸÎ£"]="Î¦Î©";
		$step1list["Î¦Î©Î¤Î‘"]="Î¦Î©";
		$step1list["Î¦Î©Î¤Î©Î"]="Î¦Î©";
		$step1list["ÎšÎ‘Î˜Î•Î£Î¤Î©Î£"]="ÎšÎ‘Î˜Î•Î£Î¤";
		$step1list["ÎšÎ‘Î˜Î•Î£Î¤Î©Î¤ÎŸÎ£"]="ÎšÎ‘Î˜Î•Î£Î¤";
		$step1list["ÎšÎ‘Î˜Î•Î£Î¤Î©Î¤Î‘"]="ÎšÎ‘Î˜Î•Î£Î¤";
		$step1list["ÎšÎ‘Î˜Î•Î£Î¤Î©Î¤Î©Î"]="ÎšÎ‘Î˜Î•Î£Î¤";
		$step1list["Î“Î•Î“ÎŸÎÎŸÎ£"]="Î“Î•Î“ÎŸÎ";
		$step1list["Î“Î•Î“ÎŸÎÎŸÎ¤ÎŸÎ£"]="Î“Î•Î“ÎŸÎ";
		$step1list["Î“Î•Î“ÎŸÎÎŸÎ¤Î‘"]="Î“Î•Î“ÎŸÎ";
		$step1list["Î“Î•Î“ÎŸÎÎŸÎ¤Î©Î"]="Î“Î•Î“ÎŸÎ";

		$v = '(Î‘|Î•|Î—|Î™|ÎŸ|Î¥|Î©)';	// vowel
		$v2 = '(Î‘|Î•|Î—|Î™|ÎŸ|Î©)'; //vowel without Y

		$test1 = true;


		//Step S1. 14 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™Î–Î‘|Î™Î–Î•Î£|Î™Î–Î•|Î™Î–Î‘ÎœÎ•|Î™Î–Î‘Î¤Î•|Î™Î–Î‘Î|Î™Î–Î‘ÎÎ•|Î™Î–Î©|Î™Î–Î•Î™Î£|Î™Î–Î•Î™|Î™Î–ÎŸÎ¥ÎœÎ•|Î™Î–Î•Î¤Î•|Î™Î–ÎŸÎ¥Î|Î™Î–ÎŸÎ¥ÎÎ•)$/';
		$exceptS1 = '/^(Î‘ÎÎ‘ÎœÎ Î‘|Î•ÎœÎ Î‘|Î•Î Î‘|ÎžÎ‘ÎÎ‘Î Î‘|Î Î‘|Î Î•Î¡Î™Î Î‘|Î‘Î˜Î¡ÎŸ|Î£Î¥ÎÎ‘Î˜Î¡ÎŸ|Î”Î‘ÎÎ•)$/';
		$exceptS2 = '/^(ÎœÎ‘Î¡Îš|ÎšÎŸÎ¡Î|Î‘ÎœÎ Î‘Î¡|Î‘Î¡Î¡|Î’Î‘Î˜Î¥Î¡Î™|Î’Î‘Î¡Îš|Î’|Î’ÎŸÎ›Î’ÎŸÎ¡|Î“ÎšÎ¡|Î“Î›Î¥ÎšÎŸÎ¡|Î“Î›Î¥ÎšÎ¥Î¡|Î™ÎœÎ |Î›|Î›ÎŸÎ¥|ÎœÎ‘Î¡|Îœ|Î Î¡|ÎœÎ Î¡|Î ÎŸÎ›Î¥Î¡|Î |Î¡|Î Î™Î Î•Î¡ÎŸÎ¡)$/';
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem . $step1list[$suffix];
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w = $w . 'I';
			}
			if( preg_match($exceptS2,$w) ) {
				$w = $w . 'IÎ–';
			}

			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}

		//Step S2. 7 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î©Î˜Î—ÎšÎ‘|Î©Î˜Î—ÎšÎ•Î£|Î©Î˜Î—ÎšÎ•|Î©Î˜Î—ÎšÎ‘ÎœÎ•|Î©Î˜Î—ÎšÎ‘Î¤Î•|Î©Î˜Î—ÎšÎ‘Î|Î©Î˜Î—ÎšÎ‘ÎÎ•)$/';
		$exceptS1 = '/^(Î‘Î›|Î’Î™|Î•Î|Î¥Î¨|Î›Î™|Î–Î©|Î£|Î§)$/';
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem . $step1list[$suffix];
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w = $w . 'Î©Î';
			}

			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}

		//Step S3. 7 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™Î£Î‘|Î™Î£Î•Î£|Î™Î£Î•|Î™Î£Î‘ÎœÎ•|Î™Î£Î‘Î¤Î•|Î™Î£Î‘Î|Î™Î£Î‘ÎÎ•)$/';
		$exceptS1 = '/^(Î‘ÎÎ‘ÎœÎ Î‘|Î‘Î˜Î¡ÎŸ|Î•ÎœÎ Î‘|Î•Î£Î•|Î•Î£Î©ÎšÎ›Î•|Î•Î Î‘|ÎžÎ‘ÎÎ‘Î Î‘|Î•Î Î•|Î Î•Î¡Î™Î Î‘|Î‘Î˜Î¡ÎŸ|Î£Î¥ÎÎ‘Î˜Î¡ÎŸ|Î”Î‘ÎÎ•|ÎšÎ›Î•|Î§Î‘Î¡Î¤ÎŸÎ Î‘|Î•ÎžÎ‘Î¡Î§Î‘|ÎœÎ•Î¤Î•Î Î•|Î‘Î ÎŸÎšÎ›Î•|Î‘Î Î•ÎšÎ›Î•|Î•ÎšÎ›Î•|Î Î•|Î Î•Î¡Î™Î Î‘)$/';
		$exceptS2 = '/^(Î‘Î|Î‘Î¦|Î“Î•|Î“Î™Î“Î‘ÎÎ¤ÎŸÎ‘Î¦|Î“ÎšÎ•|Î”Î—ÎœÎŸÎšÎ¡Î‘Î¤|ÎšÎŸÎœ|Î“Îš|Îœ|Î |Î ÎŸÎ¥ÎšÎ‘Îœ|ÎŸÎ›ÎŸ|Î›Î‘Î¡)$/';

		if ($w=="Î™Î£Î‘"){$w="Î™Î£";return $w;}
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem . $step1list[$suffix];
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w = $w . 'Î™';
			}

			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}


		//Step S4. 7 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™Î£Î©|Î™Î£Î•Î™Î£|Î™Î£Î•Î™|Î™Î£ÎŸÎ¥ÎœÎ•|Î™Î£Î•Î¤Î•|Î™Î£ÎŸÎ¥Î|Î™Î£ÎŸÎ¥ÎÎ•)$/';
		$exceptS1 = '/^(Î‘ÎÎ‘ÎœÎ Î‘|Î•ÎœÎ Î‘|Î•Î£Î•|Î•Î£Î©ÎšÎ›Î•|Î•Î Î‘|ÎžÎ‘ÎÎ‘Î Î‘|Î•Î Î•|Î Î•Î¡Î™Î Î‘|Î‘Î˜Î¡ÎŸ|Î£Î¥ÎÎ‘Î˜Î¡ÎŸ|Î”Î‘ÎÎ•|ÎšÎ›Î•|Î§Î‘Î¡Î¤ÎŸÎ Î‘|Î•ÎžÎ‘Î¡Î§Î‘|ÎœÎ•Î¤Î•Î Î•|Î‘Î ÎŸÎšÎ›Î•|Î‘Î Î•ÎšÎ›Î•|Î•ÎšÎ›Î•|Î Î•|Î Î•Î¡Î™Î Î‘)$/';

		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem . $step1list[$suffix];
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w = $w . 'Î™';
			}
			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}
		//Step S5. 11 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™Î£Î¤ÎŸÎ£|Î™Î£Î¤ÎŸÎ¥|Î™Î£Î¤ÎŸ|Î™Î£Î¤Î•|Î™Î£Î¤ÎŸÎ™|Î™Î£Î¤Î©Î|Î™Î£Î¤ÎŸÎ¥Î£|Î™Î£Î¤Î—|Î™Î£Î¤Î—Î£|Î™Î£Î¤Î‘|Î™Î£Î¤Î•Î£)$/';
		$exceptS1 = '/^(Îœ|Î |Î‘Î |Î‘Î¡|Î—Î”|ÎšÎ¤|Î£Îš|Î£Î§|Î¥Î¨|Î¦Î‘|Î§Î¡|Î§Î¤|Î‘ÎšÎ¤|Î‘ÎŸÎ¡|Î‘Î£Î§|Î‘Î¤Î‘|Î‘Î§Î|Î‘Î§Î¤|Î“Î•Îœ|Î“Î¥Î¡|Î•ÎœÎ |Î•Î¥Î |Î•Î§Î˜|Î—Î¦Î‘|Î‰Î¦Î‘|ÎšÎ‘Î˜|ÎšÎ‘Îš|ÎšÎ¥Î›|Î›Î¥Î“|ÎœÎ‘Îš|ÎœÎ•Î“|Î¤Î‘Î§|Î¦Î™Î›|Î§Î©Î¡)$/';
		$exceptS2 = '/^(Î”Î‘ÎÎ•|Î£Î¥ÎÎ‘Î˜Î¡ÎŸ|ÎšÎ›Î•|Î£Î•|Î•Î£Î©ÎšÎ›Î•|Î‘Î£Î•|Î Î›Î•)$/';
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem . $step1list[$suffix];
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w = $w . 'Î™Î£Î¤';
			}
			if( preg_match($exceptS2,$w) ) {
				$w = $w . 'Î™';
			}
			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}
		//Step S6. 6 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™Î£ÎœÎŸ|Î™Î£ÎœÎŸÎ™|Î™Î£ÎœÎŸÎ£|Î™Î£ÎœÎŸÎ¥|Î™Î£ÎœÎŸÎ¥Î£|Î™Î£ÎœÎ©Î)$/';
		$exceptS1 = '/^(Î‘Î“ÎÎ©Î£Î¤Î™Îš|Î‘Î¤ÎŸÎœÎ™Îš|Î“ÎÎ©Î£Î¤Î™Îš|Î•Î˜ÎÎ™Îš|Î•ÎšÎ›Î•ÎšÎ¤Î™Îš|Î£ÎšÎ•Î Î¤Î™Îš|Î¤ÎŸÎ Î™Îš)$/';
		$exceptS2 = '/^(Î£Î•|ÎœÎ•Î¤Î‘Î£Î•|ÎœÎ™ÎšÎ¡ÎŸÎ£Î•|Î•Î“ÎšÎ›Î•|Î‘Î ÎŸÎšÎ›Î•)$/';
		$exceptS3 = '/^(Î”Î‘ÎÎ•|Î‘ÎÎ¤Î™Î”Î‘ÎÎ•)$/';
		$exceptS4 = '/^(Î‘Î›Î•ÎžÎ‘ÎÎ”Î¡Î™Î|Î’Î¥Î–Î‘ÎÎ¤Î™Î|Î˜Î•Î‘Î¤Î¡Î™Î)$/';
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem ;
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w=str_replace('Î™Îš',"",$w);
			}
			if( preg_match($exceptS2,$w) ) {
				$w=$w."Î™Î£Îœ";
			}
			if( preg_match($exceptS3,$w) ) {
				$w=$w."Î™";
			}
			if( preg_match($exceptS4,$w) ) {
				$w=str_replace('Î™Î',"",$w);
			}
			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}

		//Step S7. 4 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î‘Î¡Î‘ÎšÎ™|Î‘Î¡Î‘ÎšÎ™Î‘|ÎŸÎ¥Î”Î‘ÎšÎ™|ÎŸÎ¥Î”Î‘ÎšÎ™Î‘)$/';
		$exceptS1 = '/^(Î£|Î§)$/';
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem ;
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w=$w."AÎ¡Î‘Îš";
			}

			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}



		//Step S8. 8 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î‘ÎšÎ™|Î‘ÎšÎ™Î‘|Î™Î¤Î£Î‘|Î™Î¤Î£Î‘Î£|Î™Î¤Î£Î•Î£|Î™Î¤Î£Î©Î|Î‘Î¡Î‘ÎšÎ™|Î‘Î¡Î‘ÎšÎ™Î‘)$/';
		$exceptS1 = '/^(Î‘ÎÎ˜Î¡|Î’Î‘ÎœÎ’|Î’Î¡|ÎšÎ‘Î™Îœ|ÎšÎŸÎ|ÎšÎŸÎ¡|Î›Î‘Î’Î¡|Î›ÎŸÎ¥Î›|ÎœÎ•Î¡|ÎœÎŸÎ¥Î£Î¤|ÎÎ‘Î“ÎšÎ‘Î£|Î Î›|Î¡|Î¡Î¥|Î£|Î£Îš|Î£ÎŸÎš|Î£Î Î‘Î|Î¤Î–|Î¦Î‘Î¡Îœ|Î§|ÎšÎ‘Î Î‘Îš|Î‘Î›Î™Î£Î¦|Î‘ÎœÎ’Î¡|Î‘ÎÎ˜Î¡|Îš|Î¦Î¥Î›|ÎšÎ‘Î¤Î¡Î‘Î |ÎšÎ›Î™Îœ|ÎœÎ‘Î›|Î£Î›ÎŸÎ’|Î¦|Î£Î¦|Î¤Î£Î•Î§ÎŸÎ£Î›ÎŸÎ’)$/';
		$exceptS2 = '/^(Î’|Î’Î‘Î›|Î“Î™Î‘Î|Î“Î›|Î–|Î—Î“ÎŸÎ¥ÎœÎ•Î|ÎšÎ‘Î¡Î”|ÎšÎŸÎ|ÎœÎ‘ÎšÎ¡Î¥Î|ÎÎ¥Î¦|Î Î‘Î¤Î•Î¡|Î |Î£Îš|Î¤ÎŸÎ£|Î¤Î¡Î™Î ÎŸÎ›)$/';
		$exceptS3 = '/(ÎšÎŸÎ¡)$/';// for words like Î Î›ÎŸÎ¥Î£Î™ÎŸÎšÎŸÎ¡Î™Î¤Î£Î‘, Î Î‘Î›Î™ÎŸÎšÎŸÎ¡Î™Î¤Î£Î‘ etc
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem ;
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w=$w."Î‘Îš";
			}
			if( preg_match($exceptS2,$w) ) {
				$w=$w."Î™Î¤Î£";
			}
			if( preg_match($exceptS3,$w) ) {
				$w=$w."Î™Î¤Î£";
			}
			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}

		//Step S9. 3 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™Î”Î™ÎŸ|Î™Î”Î™Î‘|Î™Î”Î™Î©Î)$/';
		$exceptS1 = '/^(Î‘Î™Î¦Î|Î™Î¡|ÎŸÎ›ÎŸ|Î¨Î‘Î›)$/';
		$exceptS2 = '/(Î•|Î Î‘Î™Î§Î)$/';
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem ;
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w=$w."Î™Î”";
			}
			if( preg_match($exceptS2,$w) ) {
				$w=$w."Î™Î”";
			}
			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}



		//Step S10. 4 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™Î£ÎšÎŸÎ£|Î™Î£ÎšÎŸÎ¥|Î™Î£ÎšÎŸ|Î™Î£ÎšÎ•)$/';
		$exceptS1 = '/^(Î”|Î™Î’|ÎœÎ—Î|Î¡|Î¦Î¡Î‘Î“Îš|Î›Î¥Îš|ÎŸÎ’Î•Î›)$/';
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem ;
			$test1 = false;
			if( preg_match($exceptS1,$w) ) {
				$w=$w."Î™Î£Îš";
			}

			return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
		}






		//Step1
		$numberOfRulesExamined++;
		$re = '/(.*)(Î¦Î‘Î“Î™Î‘|Î¦Î‘Î“Î™ÎŸÎ¥|Î¦Î‘Î“Î™Î©Î|Î£ÎšÎ‘Î“Î™Î‘|Î£ÎšÎ‘Î“Î™ÎŸÎ¥|Î£ÎšÎ‘Î“Î™Î©Î|ÎŸÎ›ÎŸÎ“Î™ÎŸÎ¥|ÎŸÎ›ÎŸÎ“Î™Î‘|ÎŸÎ›ÎŸÎ“Î™Î©Î|Î£ÎŸÎ“Î™ÎŸÎ¥|Î£ÎŸÎ“Î™Î‘|Î£ÎŸÎ“Î™Î©Î|Î¤Î‘Î¤ÎŸÎ“Î™Î‘|Î¤Î‘Î¤ÎŸÎ“Î™ÎŸÎ¥|Î¤Î‘Î¤ÎŸÎ“Î™Î©Î|ÎšÎ¡Î•Î‘Î£|ÎšÎ¡Î•Î‘Î¤ÎŸÎ£|ÎšÎ¡Î•Î‘Î¤Î‘|ÎšÎ¡Î•Î‘Î¤Î©Î|Î Î•Î¡Î‘Î£|Î Î•Î¡Î‘Î¤ÎŸÎ£|Î Î•Î¡Î‘Î¤Î—|Î Î•Î¡Î‘Î¤Î‘|Î Î•Î¡Î‘Î¤Î©Î|Î¤Î•Î¡Î‘Î£|Î¤Î•Î¡Î‘Î¤ÎŸÎ£|Î¤Î•Î¡Î‘Î¤Î‘|Î¤Î•Î¡Î‘Î¤Î©Î|Î¦Î©Î£|Î¦Î©Î¤ÎŸÎ£|Î¦Î©Î¤Î‘|Î¦Î©Î¤Î©Î|ÎšÎ‘Î˜Î•Î£Î¤Î©Î£|ÎšÎ‘Î˜Î•Î£Î¤Î©Î¤ÎŸÎ£|ÎšÎ‘Î˜Î•Î£Î¤Î©Î¤Î‘|ÎšÎ‘Î˜Î•Î£Î¤Î©Î¤Î©Î|Î“Î•Î“ÎŸÎÎŸÎ£|Î“Î•Î“ÎŸÎÎŸÎ¤ÎŸÎ£|Î“Î•Î“ÎŸÎÎŸÎ¤Î‘|Î“Î•Î“ÎŸÎÎŸÎ¤Î©Î)$/';





		if (preg_match($re,$w,$match)) {
			$stem = $match[1];
			$suffix = $match[2];
			$w = $stem . $step1list[$suffix];
			$test1 = false;

		}


		// Step 2a. 2 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î‘Î”Î•Î£|Î‘Î”Î©Î)$/';
		if( preg_match($re,$w,$match) ) {
			$stem = $match[1];
			$w = $stem;
			$re = '/(ÎŸÎš|ÎœÎ‘Îœ|ÎœÎ‘Î|ÎœÎ Î‘ÎœÎ |Î Î‘Î¤Î•Î¡|Î“Î™Î‘Î“Î™|ÎÎ¤Î‘ÎÎ¤|ÎšÎ¥Î¡|Î˜Î•Î™|Î Î•Î˜Î•Î¡)$/';
			if( !preg_match($re,$w) ) {
				$w = $w . "Î‘Î”";
			}


		}

		//Step 2b. 2 stems
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î•Î”Î•Î£|Î•Î”Î©Î)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$exept2 = '/(ÎŸÎ |Î™Î |Î•ÎœÎ |Î¥Î |Î“Î—Î |Î”Î‘Î |ÎšÎ¡Î‘Î£Î |ÎœÎ™Î›)$/';
			if( preg_match($exept2,$w) ) {
				$w = $w . 'Î•Î”';
			}

		}

		//Step 2c
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ÎŸÎ¥Î”Î•Î£|ÎŸÎ¥Î”Î©Î)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;

			$exept3 = '/(Î‘Î¡Îš|ÎšÎ‘Î›Î™Î‘Îš|Î Î•Î¤Î‘Î›|Î›Î™Î§|Î Î›Î•Îž|Î£Îš|Î£|Î¦Î›|Î¦Î¡|Î’Î•Î›|Î›ÎŸÎ¥Î›|Î§Î|Î£Î |Î¤Î¡Î‘Î“|Î¦Î•)$/';
			if( preg_match($exept3,$w) ) {
				$w = $w . 'ÎŸÎ¥Î”';
			}

		}

		//Step 2d
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î•Î©Î£|Î•Î©Î)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept4 = '/^(Î˜|Î”|Î•Î›|Î“Î‘Î›|Î|Î |Î™Î”|Î Î‘Î¡)$/';
			if( preg_match($exept4,$w) ) {
				$w = $w . 'Î•';
			}

		}

		//Step 3
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™Î‘|Î™ÎŸÎ¥|Î™Î©Î)$/';
		if( preg_match($re,$w,$fp) ) {
			$stem = $fp[1];
			$w = $stem;
			$re = '/'.$v.'$/';
			$test1 = false;
			if( preg_match($re,$w) ) {
				$w = $stem . 'Î™';
			}
		}

		//Step 4
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î™ÎšÎ‘|Î™ÎšÎŸ|Î™ÎšÎŸÎ¥|Î™ÎšÎ©Î)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;

			$test1 = false;
			$re = '/'.$v.'$/';
			$exept5 = '/^(Î‘Î›|Î‘Î”|Î•ÎÎ”|Î‘ÎœÎ‘Î|Î‘ÎœÎœÎŸÎ§Î‘Î›|Î—Î˜|Î‘ÎÎ—Î˜|Î‘ÎÎ¤Î™Î”|Î¦Î¥Î£|Î’Î¡Î©Îœ|Î“Î•Î¡|Î•ÎžÎ©Î”|ÎšÎ‘Î›Î |ÎšÎ‘Î›Î›Î™Î|ÎšÎ‘Î¤Î‘Î”|ÎœÎŸÎ¥Î›|ÎœÎ Î‘Î|ÎœÎ Î‘Î“Î™Î‘Î¤|ÎœÎ ÎŸÎ›|ÎœÎ ÎŸÎ£|ÎÎ™Î¤|ÎžÎ™Îš|Î£Î¥ÎÎŸÎœÎ—Î›|Î Î•Î¤Î£|Î Î™Î¤Î£|Î Î™ÎšÎ‘ÎÎ¤|Î Î›Î™Î‘Î¤Î£|Î ÎŸÎ£Î¤Î•Î›Î|Î Î¡Î©Î¤ÎŸÎ”|Î£Î•Î¡Î¤|Î£Î¥ÎÎ‘Î”|Î¤Î£Î‘Îœ|Î¥Î ÎŸÎ”|Î¦Î™Î›ÎŸÎ|Î¦Î¥Î›ÎŸÎ”|Î§Î‘Î£)$/';
			if( preg_match($re,$w) || preg_match($exept5,$w) ) {
				$w = $w . 'Î™Îš';
			}
		}

		//step 5a
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î‘ÎœÎ•)$/';
		$re2 = '/^(.+?)(Î‘Î“Î‘ÎœÎ•|Î—Î£Î‘ÎœÎ•|ÎŸÎ¥Î£Î‘ÎœÎ•|Î—ÎšÎ‘ÎœÎ•|Î—Î˜Î—ÎšÎ‘ÎœÎ•)$/';
		if ($w == "Î‘Î“Î‘ÎœÎ•") {
			$w = "Î‘Î“Î‘Îœ";

		}

		if( preg_match($re2,$w) ) {
			preg_match($re2,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;
		}
		$numberOfRulesExamined++;
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept6 = '/^(Î‘ÎÎ‘Î |Î‘Î ÎŸÎ˜|Î‘Î ÎŸÎš|Î‘Î ÎŸÎ£Î¤|Î’ÎŸÎ¥Î’|ÎžÎ•Î˜|ÎŸÎ¥Î›|Î Î•Î˜|Î Î™ÎšÎ¡|Î ÎŸÎ¤|Î£Î™Î§|Î§)$/';
			if( preg_match($exept6,$w) ) {
				$w = $w . "Î‘Îœ";
			}
		}

		//Step 5b
		$numberOfRulesExamined++;
		$re2 = '/^(.+?)(Î‘ÎÎ•)$/';
		$re3 = '/^(.+?)(Î‘Î“Î‘ÎÎ•|Î—Î£Î‘ÎÎ•|ÎŸÎ¥Î£Î‘ÎÎ•|Î™ÎŸÎÎ¤Î‘ÎÎ•|Î™ÎŸÎ¤Î‘ÎÎ•|Î™ÎŸÎ¥ÎÎ¤Î‘ÎÎ•|ÎŸÎÎ¤Î‘ÎÎ•|ÎŸÎ¤Î‘ÎÎ•|ÎŸÎ¥ÎÎ¤Î‘ÎÎ•|Î—ÎšÎ‘ÎÎ•|Î—Î˜Î—ÎšÎ‘ÎÎ•)$/';

		if( preg_match($re3,$w) ) {
			preg_match($re3,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$re3 = '/^(Î¤Î¡|Î¤Î£)$/';
			if( preg_match($re3,$w) ) {
				$w = $w .  "Î‘Î“Î‘Î";
			}
		}
		$numberOfRulesExamined++;
		if( preg_match($re2,$w) ) {
			preg_match($re2,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$re2 = '/'.$v2.'$/';
			$exept7 = '/^(Î’Î•Î¤Î•Î¡|Î’ÎŸÎ¥Î›Îš|Î’Î¡Î‘Î§Îœ|Î“|Î”Î¡Î‘Î”ÎŸÎ¥Îœ|Î˜|ÎšÎ‘Î›Î ÎŸÎ¥Î–|ÎšÎ‘Î£Î¤Î•Î›|ÎšÎŸÎ¡ÎœÎŸÎ¡|Î›Î‘ÎŸÎ Î›|ÎœÎ©Î‘ÎœÎ•Î˜|Îœ|ÎœÎŸÎ¥Î£ÎŸÎ¥Î›Îœ|Î|ÎŸÎ¥Î›|Î |Î Î•Î›Î•Îš|Î Î›|Î ÎŸÎ›Î™Î£|Î ÎŸÎ¡Î¤ÎŸÎ›|Î£Î‘Î¡Î‘ÎšÎ‘Î¤Î£|Î£ÎŸÎ¥Î›Î¤|Î¤Î£Î‘Î¡Î›Î‘Î¤|ÎŸÎ¡Î¦|Î¤Î£Î™Î“Î“|Î¤Î£ÎŸÎ |Î¦Î©Î¤ÎŸÎ£Î¤Î•Î¦|Î§|Î¨Î¥Î§ÎŸÎ Î›|Î‘Î“|ÎŸÎ¡Î¦|Î“Î‘Î›|Î“Î•Î¡|Î”Î•Îš|Î”Î™Î Î›|Î‘ÎœÎ•Î¡Î™ÎšÎ‘Î|ÎŸÎ¥Î¡|Î Î™Î˜|Î ÎŸÎ¥Î¡Î™Î¤|Î£|Î–Î©ÎÎ¤|Î™Îš|ÎšÎ‘Î£Î¤|ÎšÎŸÎ |Î›Î™Î§|Î›ÎŸÎ¥Î˜Î—Î¡|ÎœÎ‘Î™ÎÎ¤|ÎœÎ•Î›|Î£Î™Î“|Î£Î |Î£Î¤Î•Î“|Î¤Î¡Î‘Î“|Î¤Î£Î‘Î“|Î¦|Î•Î¡|Î‘Î”Î‘Î |Î‘Î˜Î™Î“Î“|Î‘ÎœÎ—Î§|Î‘ÎÎ™Îš|Î‘ÎÎŸÎ¡Î“|Î‘Î Î—Î“|Î‘Î Î™Î˜|Î‘Î¤Î£Î™Î“Î“|Î’Î‘Î£|Î’Î‘Î£Îš|Î’Î‘Î˜Î¥Î“Î‘Î›|Î’Î™ÎŸÎœÎ—Î§|Î’Î¡Î‘Î§Î¥Îš|Î”Î™Î‘Î¤|Î”Î™Î‘Î¦|Î•ÎÎŸÎ¡Î“|Î˜Î¥Î£|ÎšÎ‘Î ÎÎŸÎ’Î™ÎŸÎœÎ—Î§|ÎšÎ‘Î¤Î‘Î“Î‘Î›|ÎšÎ›Î™Î’|ÎšÎŸÎ™Î›Î‘Î¡Î¦|Î›Î™Î’|ÎœÎ•Î“Î›ÎŸÎ’Î™ÎŸÎœÎ—Î§|ÎœÎ™ÎšÎ¡ÎŸÎ’Î™ÎŸÎœÎ—Î§|ÎÎ¤Î‘Î’|ÎžÎ—Î¡ÎŸÎšÎ›Î™Î’|ÎŸÎ›Î™Î“ÎŸÎ”Î‘Îœ|ÎŸÎ›ÎŸÎ“Î‘Î›|Î Î•ÎÎ¤Î‘Î¡Î¦|Î Î•Î¡Î—Î¦|Î Î•Î¡Î™Î¤Î¡|Î Î›Î‘Î¤|Î ÎŸÎ›Î¥Î”Î‘Î |Î ÎŸÎ›Î¥ÎœÎ—Î§|Î£Î¤Î•Î¦|Î¤Î‘Î’|Î¤Î•Î¤|Î¥Î Î•Î¡Î—Î¦|Î¥Î ÎŸÎšÎŸÎ |Î§Î‘ÎœÎ—Î›ÎŸÎ”Î‘Î |Î¨Î—Î›ÎŸÎ¤Î‘Î’)$/';
			if( preg_match($re2,$w) || preg_match($exept7,$w) ){
				$w = $w .  "Î‘Î";
			}
		}

		//Step 5c
		$numberOfRulesExamined++;
		$re3 = '/^(.+?)(Î•Î¤Î•)$/';
		$re4 = '/^(.+?)(Î—Î£Î•Î¤Î•)$/';

		if( preg_match($re4,$w) ) {
			preg_match($re4,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;
		}
		$numberOfRulesExamined++;
		if( preg_match($re3,$w) ) {
			preg_match($re3,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$re3 = '/'.$v2.'$/';
			$exept8 =  '/(ÎŸÎ”|Î‘Î™Î¡|Î¦ÎŸÎ¡|Î¤Î‘Î˜|Î”Î™Î‘Î˜|Î£Î§|Î•ÎÎ”|Î•Î¥Î¡|Î¤Î™Î˜|Î¥Î Î•Î¡Î˜|Î¡Î‘Î˜|Î•ÎÎ˜|Î¡ÎŸÎ˜|Î£Î˜|Î Î¥Î¡|Î‘Î™Î|Î£Î¥ÎÎ”|Î£Î¥Î|Î£Î¥ÎÎ˜|Î§Î©Î¡|Î ÎŸÎ|Î’Î¡|ÎšÎ‘Î˜|Î•Î¥Î˜|Î•ÎšÎ˜|ÎÎ•Î¤|Î¡ÎŸÎ|Î‘Î¡Îš|Î’Î‘Î¡|Î’ÎŸÎ›|Î©Î¦Î•Î›)$/';
			$exept9 = '/^(Î‘Î’Î‘Î¡|Î’Î•Î|Î•ÎÎ‘Î¡|Î‘Î’Î¡|Î‘Î”|Î‘Î˜|Î‘Î|Î‘Î Î›|Î’Î‘Î¡ÎŸÎ|ÎÎ¤Î¡|Î£Îš|ÎšÎŸÎ |ÎœÎ ÎŸÎ¡|ÎÎ™Î¦|Î Î‘Î“|Î Î‘Î¡Î‘ÎšÎ‘Î›|Î£Î•Î¡Î |Î£ÎšÎ•Î›|Î£Î¥Î¡Î¦|Î¤ÎŸÎš|Î¥|Î”|Î•Îœ|Î˜Î‘Î¡Î¡|Î˜)$/';

			if( preg_match($re3,$w) || preg_match($exept8,$w) || preg_match($exept9,$w) ){
				$w = $w .  "Î•Î¤";
			}
		}

		//Step 5d
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ÎŸÎÎ¤Î‘Î£|Î©ÎÎ¤Î‘Î£)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept10 = '/^(Î‘Î¡Î§)$/';
			$exept11 = '/(ÎšÎ¡Î•)$/';
			if( preg_match($exept10,$w) ){
				$w = $w . "ÎŸÎÎ¤";
			}
			if( preg_match($exept11,$w) ){
				$w = $w . "Î©ÎÎ¤";
			}
		}

		//Step 5e
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ÎŸÎœÎ‘Î£Î¤Î•|Î™ÎŸÎœÎ‘Î£Î¤Î•)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept11 = '/^(ÎŸÎ)$/';
			if( preg_match($exept11,$w) ){
				$w = $w .  "ÎŸÎœÎ‘Î£Î¤";
			}
		}

		//Step 5f
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î•Î£Î¤Î•)$/';
		$re2 = '/^(.+?)(Î™Î•Î£Î¤Î•)$/';

		if( preg_match($re2,$w) ) {
			preg_match($re2,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$re2 = '/^(Î |Î‘Î |Î£Î¥ÎœÎ |Î‘Î£Î¥ÎœÎ |Î‘ÎšÎ‘Î¤Î‘Î |Î‘ÎœÎ•Î¤Î‘ÎœÎ¦)$/';
			if( preg_match($re2,$w) ) {
				$w = $w . "Î™Î•Î£Î¤";
			}
		}
		$numberOfRulesExamined++;
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept12 = '/^(Î‘Î›|Î‘Î¡|Î•ÎšÎ¤Î•Î›|Î–|Îœ|Îž|Î Î‘Î¡Î‘ÎšÎ‘Î›|Î‘Î¡|Î Î¡ÎŸ|ÎÎ™Î£)$/';
			if( preg_match($exept12,$w) ){
				$w = $w . "Î•Î£Î¤";
			}
		}

		//Step 5g
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î—ÎšÎ‘|Î—ÎšÎ•Î£|Î—ÎšÎ•)$/';
		$re2 = '/^(.+?)(Î—Î˜Î—ÎšÎ‘|Î—Î˜Î—ÎšÎ•Î£|Î—Î˜Î—ÎšÎ•)$/';

		if( preg_match($re2,$w) ) {
			preg_match($re2,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;
		}
		$numberOfRulesExamined++;
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept13 = '/(Î£ÎšÎ©Î›|Î£ÎšÎŸÎ¥Î›|ÎÎ‘Î¡Î˜|Î£Î¦|ÎŸÎ˜|Î Î™Î˜)$/';
			$exept14 = '/^(Î”Î™Î‘Î˜|Î˜|Î Î‘Î¡Î‘ÎšÎ‘Î¤Î‘Î˜|Î Î¡ÎŸÎ£Î˜|Î£Î¥ÎÎ˜|)$/';
			if( preg_match($exept13,$w) || preg_match($exept14,$w) ){
				$w = $w . "Î—Îš";
			}
		}


		//Step 5h
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ÎŸÎ¥Î£Î‘|ÎŸÎ¥Î£Î•Î£|ÎŸÎ¥Î£Î•)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept15 = '/^(Î¦Î‘Î¡ÎœÎ‘Îš|Î§Î‘Î”|Î‘Î“Îš|Î‘ÎÎ‘Î¡Î¡|Î’Î¡ÎŸÎœ|Î•ÎšÎ›Î™Î |Î›Î‘ÎœÎ Î™Î”|Î›Î•Î§|Îœ|Î Î‘Î¤|Î¡|Î›|ÎœÎ•Î”|ÎœÎ•Î£Î‘Î–|Î¥Î ÎŸÎ¤Î•Î™Î|Î‘Îœ|Î‘Î™Î˜|Î‘ÎÎ—Îš|Î”Î•Î£Î ÎŸÎ–|Î•ÎÎ”Î™Î‘Î¦Î•Î¡|Î”Î•|Î”Î•Î¥Î¤Î•Î¡Î•Î¥|ÎšÎ‘Î˜Î‘Î¡Î•Î¥|Î Î›Î•|Î¤Î£Î‘)$/';
			$exept16 = '/(Î ÎŸÎ”Î‘Î¡|Î’Î›Î•Î |Î Î‘ÎÎ¤Î‘Î§|Î¦Î¡Î¥Î”|ÎœÎ‘ÎÎ¤Î™Î›|ÎœÎ‘Î›Î›|ÎšÎ¥ÎœÎ‘Î¤|Î›Î‘Î§|Î›Î—Î“|Î¦Î‘Î“|ÎŸÎœ|Î Î¡Î©Î¤)$/';
			if( preg_match($exept15,$w) || preg_match($exept16,$w) ){
				$w = $w . "ÎŸÎ¥Î£";
			}
		}

		//Step 5i
		$re = '/^(.+?)(Î‘Î“Î‘|Î‘Î“Î•Î£|Î‘Î“Î•)$/';
		$numberOfRulesExamined++;
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept17 = '/^(Î¨ÎŸÎ¦|ÎÎ‘Î¥Î›ÎŸÎ§)$/';
			$exept20 = '/(ÎšÎŸÎ›Î›)$/';
			$exept18 = '/^(Î‘Î’Î‘Î£Î¤|Î ÎŸÎ›Î¥Î¦|Î‘Î”Î—Î¦|Î Î‘ÎœÎ¦|Î¡|Î‘Î£Î |Î‘Î¦|Î‘ÎœÎ‘Î›|Î‘ÎœÎ‘Î›Î›Î™|Î‘ÎÎ¥Î£Î¤|Î‘Î Î•Î¡|Î‘Î£Î Î‘Î¡|Î‘Î§Î‘Î¡|Î”Î•Î¡Î’Î•Î|Î”Î¡ÎŸÎ£ÎŸÎ |ÎžÎ•Î¦|ÎÎ•ÎŸÎ |ÎÎŸÎœÎŸÎ¤|ÎŸÎ›ÎŸÎ |ÎŸÎœÎŸÎ¤|Î Î¡ÎŸÎ£Î¤|Î Î¡ÎŸÎ£Î©Î ÎŸÎ |Î£Î¥ÎœÎ |Î£Î¥ÎÎ¤|Î¤|Î¥Î ÎŸÎ¤|Î§Î‘Î¡|Î‘Î•Î™Î |Î‘Î™ÎœÎŸÎ£Î¤|Î‘ÎÎ¥Î |Î‘Î ÎŸÎ¤|Î‘Î¡Î¤Î™Î |Î”Î™Î‘Î¤|Î•Î|Î•Î Î™Î¤|ÎšÎ¡ÎŸÎšÎ‘Î›ÎŸÎ |Î£Î™Î”Î—Î¡ÎŸÎ |Î›|ÎÎ‘Î¥|ÎŸÎ¥Î›Î‘Îœ|ÎŸÎ¥Î¡|Î |Î¤Î¡|Îœ)$/';
			$exept19 = '/(ÎŸÎ¦|Î Î•Î›|Î§ÎŸÎ¡Î¤|Î›Î›|Î£Î¦|Î¡Î |Î¦Î¡|Î Î¡|Î›ÎŸÎ§|Î£ÎœÎ—Î)$/';

			if( (preg_match($exept18,$w) || preg_match($exept19,$w))
				&& !(preg_match($exept17,$w) || preg_match($exept20,$w)) ) {
				$w = $w . "Î‘Î“";
			}
		}


		//Step 5j
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î—Î£Î•|Î—Î£ÎŸÎ¥|Î—Î£Î‘)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept21 = '/^(Î|Î§Î•Î¡Î£ÎŸÎ|Î”Î©Î”Î•ÎšÎ‘Î|Î•Î¡Î—ÎœÎŸÎ|ÎœÎ•Î“Î‘Î›ÎŸÎ|Î•Î Î¤Î‘Î)$/';
			if( preg_match($exept21,$w) ){
				$w = $w . "Î—Î£";
			}
		}

		//Step 5k
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î—Î£Î¤Î•)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept22 = '/^(Î‘Î£Î’|Î£Î’|Î‘Î§Î¡|Î§Î¡|Î‘Î Î›|Î‘Î•Î™ÎœÎ|Î”Î¥Î£Î§Î¡|Î•Î¥Î§Î¡|ÎšÎŸÎ™ÎÎŸÎ§Î¡|Î Î‘Î›Î™ÎœÎ¨)$/';
			if( preg_match($exept22,$w) ){
				$w = $w . "Î—Î£Î¤";
			}
		}

		//Step 5l
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ÎŸÎ¥ÎÎ•|Î—Î£ÎŸÎ¥ÎÎ•|Î—Î˜ÎŸÎ¥ÎÎ•)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept23 = '/^(Î|Î¡|Î£Î Î™|Î£Î¤Î¡Î‘Î’ÎŸÎœÎŸÎ¥Î¤Î£|ÎšÎ‘ÎšÎŸÎœÎŸÎ¥Î¤Î£|Î•ÎžÎ©Î)$/';
			if( preg_match($exept23,$w) ){
				$w = $w . "ÎŸÎ¥Î";
			}
		}

		//Step 5l
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ÎŸÎ¥ÎœÎ•|Î—Î£ÎŸÎ¥ÎœÎ•|Î—Î˜ÎŸÎ¥ÎœÎ•)$/';
		if( preg_match($re,$w) ) {
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
			$test1 = false;

			$exept24 = '/^(Î Î‘Î¡Î‘Î£ÎŸÎ¥Î£|Î¦|Î§|Î©Î¡Î™ÎŸÎ Î›|Î‘Î–|Î‘Î›Î›ÎŸÎ£ÎŸÎ¥Î£|Î‘Î£ÎŸÎ¥Î£)$/';
			if( preg_match($exept24,$w) ){
				$w = $w . "ÎŸÎ¥Îœ";
			}
		}

		// Step 6
		$numberOfRulesExamined++;
		$re = '/^(.+?)(ÎœÎ‘Î¤Î‘|ÎœÎ‘Î¤Î©Î|ÎœÎ‘Î¤ÎŸÎ£)$/';
		$re2 = '/^(.+?)(Î‘|Î‘Î“Î‘Î¤Î•|Î‘Î“Î‘Î|Î‘Î•Î™|Î‘ÎœÎ‘Î™|Î‘Î|Î‘Î£|Î‘Î£Î‘Î™|Î‘Î¤Î‘Î™|Î‘Î©|Î•|Î•Î™|Î•Î™Î£|Î•Î™Î¤Î•|Î•Î£Î‘Î™|Î•Î£|Î•Î¤Î‘Î™|Î™|Î™Î•ÎœÎ‘Î™|Î™Î•ÎœÎ‘Î£Î¤Î•|Î™Î•Î¤Î‘Î™|Î™Î•Î£Î‘Î™|Î™Î•Î£Î‘Î£Î¤Î•|Î™ÎŸÎœÎ‘Î£Î¤Î‘Î|Î™ÎŸÎœÎŸÎ¥Î|Î™ÎŸÎœÎŸÎ¥ÎÎ‘|Î™ÎŸÎÎ¤Î‘Î|Î™ÎŸÎÎ¤ÎŸÎ¥Î£Î‘Î|Î™ÎŸÎ£Î‘Î£Î¤Î‘Î|Î™ÎŸÎ£Î‘Î£Î¤Î•|Î™ÎŸÎ£ÎŸÎ¥Î|Î™ÎŸÎ£ÎŸÎ¥ÎÎ‘|Î™ÎŸÎ¤Î‘Î|Î™ÎŸÎ¥ÎœÎ‘|Î™ÎŸÎ¥ÎœÎ‘Î£Î¤Î•|Î™ÎŸÎ¥ÎÎ¤Î‘Î™|Î™ÎŸÎ¥ÎÎ¤Î‘Î|Î—|Î—Î”Î•Î£|Î—Î”Î©Î|Î—Î˜Î•Î™|Î—Î˜Î•Î™Î£|Î—Î˜Î•Î™Î¤Î•|Î—Î˜Î—ÎšÎ‘Î¤Î•|Î—Î˜Î—ÎšÎ‘Î|Î—Î˜ÎŸÎ¥Î|Î—Î˜Î©|Î—ÎšÎ‘Î¤Î•|Î—ÎšÎ‘Î|Î—Î£|Î—Î£Î‘Î|Î—Î£Î‘Î¤Î•|Î—Î£Î•Î™|Î—Î£Î•Î£|Î—Î£ÎŸÎ¥Î|Î—Î£Î©|ÎŸ|ÎŸÎ™|ÎŸÎœÎ‘Î™|ÎŸÎœÎ‘Î£Î¤Î‘Î|ÎŸÎœÎŸÎ¥Î|ÎŸÎœÎŸÎ¥ÎÎ‘|ÎŸÎÎ¤Î‘Î™|ÎŸÎÎ¤Î‘Î|ÎŸÎÎ¤ÎŸÎ¥Î£Î‘Î|ÎŸÎ£|ÎŸÎ£Î‘Î£Î¤Î‘Î|ÎŸÎ£Î‘Î£Î¤Î•|ÎŸÎ£ÎŸÎ¥Î|ÎŸÎ£ÎŸÎ¥ÎÎ‘|ÎŸÎ¤Î‘Î|ÎŸÎ¥|ÎŸÎ¥ÎœÎ‘Î™|ÎŸÎ¥ÎœÎ‘Î£Î¤Î•|ÎŸÎ¥Î|ÎŸÎ¥ÎÎ¤Î‘Î™|ÎŸÎ¥ÎÎ¤Î‘Î|ÎŸÎ¥Î£|ÎŸÎ¥Î£Î‘Î|ÎŸÎ¥Î£Î‘Î¤Î•|Î¥|Î¥Î£|Î©|Î©Î)$/';
		if( preg_match($re,$w,$match))  {
			$stem = $match[1];
			$w = $stem . "ÎœÎ‘";
		}
		$numberOfRulesExamined++;
		if( preg_match($re2,$w) && $test1 ) {
			preg_match($re2,$w,$match);
			$stem = $match[1];
			$w = $stem;
		}

		// Step 7 (Î Î‘Î¡Î‘Î˜Î•Î¤Î™ÎšÎ‘)
		$numberOfRulesExamined++;
		$re = '/^(.+?)(Î•Î£Î¤Î•Î¡|Î•Î£Î¤Î‘Î¤|ÎŸÎ¤Î•Î¡|ÎŸÎ¤Î‘Î¤|Î¥Î¤Î•Î¡|Î¥Î¤Î‘Î¤|Î©Î¤Î•Î¡|Î©Î¤Î‘Î¤)$/';
		if( preg_match($re,$w) ){
			preg_match($re,$w,$match);
			$stem = $match[1];
			$w = $stem;
		}



		return returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined);
	}

	protected function returnStem($w,$w_CASE,$encoding_changed,$numberOfRulesExamined)
	{
		//convert case back to initial by reading $w_CASE
		$unacceptedLetters=array("Î±","Î²","Î³","Î´","Îµ","Î¶","Î·","Î¸","Î¹","Îº","Î»","Î¼","Î½","Î¾","Î¿","Ï€","Ï","Ïƒ","Ï„","Ï…","Ï†","Ï‡","Ïˆ","Ï‰","Î¬","Î­","Î®","Î¯","ÏŒ","Ï","Ï‚","ÏŽ","ÏŠ");
		$acceptedLetters=array("Î‘","Î’","Î“","Î”","Î•","Î–","Î—","Î˜","Î™","Îš","Î›","Îœ","Î","Îž","ÎŸ","Î ","Î¡","Î£","Î¤","Î¥","Î¦","Î§","Î¨","Î©","Î‘","Î•","Î—","Î™","ÎŸ","Î¥","Î£","Î©","Î™");
		for($i=0;$i<=strlen($w)-1;$i++){
			if (@$w_CASE[$i]==1){
				for($k=0;$k<=32;$k=$k+1){
					if ($w[$i]== $acceptedLetters[$k]){
						$w[$i]= $unacceptedLetters[$k];
					}
				}
			}
			else if (@$w_CASE[$i]==2){$w[$i]="Ï‚";}
		}

		return $w;
	}
}