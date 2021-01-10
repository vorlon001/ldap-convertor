<?php

declare(strict_types = 1);

namespace ERROR {
	class LdapThrowable extends \Exception { }
}

namespace UTILS {

	function error_point($NAMESPACE,$FUNCTION,$METHOD,$DIR,$FILE,$LINE): string {
        	return sprintf("POINT: NAMESPACE:'%s' FUNCTION:'%s' METHOD:'%s' DIR:'%s' FILE:'%s' LINE:'%s' \n",
                                                $NAMESPACE, $FUNCTION, $METHOD, $DIR, $FILE, $LINE);
	}
	function sozdat_slag(string $stroka): string {
        	try {
                	$rus=array(	'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч',
					'Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё',
					'ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
                	$lat=array(	'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t',
					'u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',
					'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch',
					'y','y','y','e','yu','ya',' ');
 	               $stroka = str_replace($rus, $lat, $stroka); // перевеодим на английский
        	        $stroka = str_replace('-', '', $stroka); // удаляем все исходные "-"
                	$slag = preg_replace('/[^A-Za-z0-9-]+/', ' ', $stroka); // заменяет все символы и пробелы на "-"
        	} catch (\Throwable $e) {
                	$errpoint = sprintf("POINT: NAMESPACE:'%s' FUNCTION:'%s' METHOD:'%s' DIR:'%s' FILE:'%s' LINE:'%s' \n",
                        	 __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__);
               		$errmsg = sprintf("%s DUMP:%s. \n ERROR: %s\n",$errpoint, $stroka, $e->getMessage() );
                	throw new \ERROR\LdapThrowable( $errmsg );
        	} finally {
                	return $slag;
        	}
        	return "";
	}
}

namespace MAPS {

	function get_create_maping(array $map): array {
		return array (
                        array('{login}',$map["login"],$file),
                        array('{displayname}',$map["displayname"],$file),
                        array('{givenname}',$map["givenname"],$file),
                        array('{mail}',$map["mail"],$file),
                        array('{mobile}',$map["mobile"],$file),
                        array('{idNumber}',$map["idNumber"],$file)
		);
	}

	function get_init_mapping(): array {
		return array (
                        "ldapgroup.fenix.ldiff",
                        "ldapuser.fenix.ldiff",
                        "modify.fenix.access.ldiff",
                        "modify.fenix.clouds.ldiff",
                        "modify.fenix.labs.ldiff"
		);
	}
}

namespace CONVERTOR {

	class GENERATOR {
                private static ?GENERATOR $instance = null;
		private $ldapconn = null;
		private function __clone() {}
		private function __sleep(): array { return array(); }
		private function __wakeup() {}
		public function __isset($name): void {}
		public function __unset($name): void {}
		public function __call($name, $arguments): mixed { return false;}
		public static function __callStatic($name, $arguments): mixed { return false; }
		public function __set($name, $value): void {}
		public function __get($name): mixed { return false; }
		private function __construct() {}

		public static function getInstance(): GENERATOR {
			try {
				if (null === self::$instance) {
            				self::$instance = new static();
				}
                        } catch (\Throwable $e) {
                		$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
                		$errmsg = sprintf("%s \n ERROR: %s\n",$errpoint,$e->getMessage() );
                                throw new \ERROR\LdapThrowable( $errmsg );
                        } finally {
				return self::$instance;
			}
		}

		private function create(array $map, string $tpl_file, string $config_file): void {
			try {
				//$a = 1/0;
				if (!file_exists($tpl_file)) {
                        		$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
					throw new \ERROR\LdapThrowable(sprintf("File is not readable.: %s \n %s".PHP_EOL, $tpl_file,$errpoint));
				} else if (!is_readable($tpl_file)) {
					$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
					throw new \ERROR\LdapThrowable(sprintf("File is not readable.: %s \n %s".PHP_EOL, $tpl_file,$errpoint));
				}

				$file = file_get_contents($tpl_file);

				$mapping = \MAPS\get_create_maping($map); 

				foreach ($mapping as &$value) {
					$file=str_replace($value[0],$value[1],$file);
				}

				// print($file); var_dump($er);
				if ( ( $er = file_put_contents($config_file, $file) ) == false) {
					$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
		        		throw new \ERROR\LdapThrowable( sprintf("WRITE ERROR ldapuser.%s.ldif \n %s ".PHP_EOL,$config_file, $errpoint) );
		    		}
			} catch (\Throwable $e) {
				$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
				$errmsg = sprintf("%s DUMP:%s,%s,%s. \n ERROR: %s\n",$errpoint,$tpl_file,$config_file,var_export($map, true),$e->getMessage() );
				throw new \ERROR\LdapThrowable( $errmsg );
			}
		}

		private function init(string $prefixtpl, array $map): void {
			try {
				$mapping = \MAPS\get_init_mapping(); 

				if (is_dir($map["login"]) && !is_writable($map["login"])) {
					$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
					throw new \ERROR\LdapThrowable( sprintf("DIRECTORY WRITE ERROR: %s \n %s",$map["login"],$errpoint));
			} else if( !is_dir( $map["login"] ) )
				mkdir( $map["login"], 0777, true );

				foreach ($mapping as &$value) {
					$file_config=sprintf("./%s/%s", $map["login"], str_replace("fenix",$map["login"],$value));
					$file_tpl   =sprintf("./%s/%s", $prefixtpl, $value );
					$this->create($map, $file_tpl, $file_config);
				}
		        } catch (\Throwable $e) {
				$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
               			$errmsg = sprintf("%s DUMP:%s. \n ERROR: %s\n",$errpoint, var_export($map, true),$e->getMessage() );
                		throw new \ERROR\LdapThrowable( $errmsg );
        		}
		}

		private function ldapconnect(string $ldaprdn, string $ldappass, string $ldapserver ): void  {
			try {

				$this->ldapconn = ldap_connect($ldapserver)
					or die("Could not connect to LDAP server.".PHP_EOL);

				ldap_set_option( $this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3 );
				ldap_set_option( $this->ldapconn, LDAP_OPT_REFERRALS, 0 );
        		} catch (\Throwable $e) {
				$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
               			$errmsg = sprintf("%s DUMP:%s,%s,%s. \n ERROR: %s\n",$errpoint, $ldapserver, $ldaprdn, $dc, $e->getMessage() );
               			throw new \ERROR\LdapThrowable( $errmsg );
        		} 
		}

                public function main(int $uIDstart, string $TPLprefix, string $ldaprdn, string $ldappass, string $ldapserver, string $dc, array $LOGINS): void {

			try {

				$this->ldapconnect($ldaprdn, $ldappass, $ldapserver);

				if ($this->ldapconn) {
					$ldapbind = @ldap_bind($this->ldapconn, $ldaprdn, $ldappass);
					if ($ldapbind) {
						print("LDAP bind successful...".PHP_EOL);
					} else {
						$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
		                		$errmsg = sprintf("%s DUMP:%s,%s,%s. \n ERROR: %s\n",$errpoint, $ldapserver, $ldaprdn, $dc, $e->getMessage() );
			       			print("LDAP bind failed...".PHP_EOL);
		                		throw new \ERROR\LdapThrowable( $errmsg );
    					}

					if ($ldapbind) {

						$attributes = array(	"displayname", "mail", "samaccountname","telephoneNumber","givenName","sN",
        	        						"userprincipalname","displayname", "ipphone", "mobile" );

						$uId = $uIDstart;
						foreach ($LOGINS as $key => $LOGIN) {
	        					$filter = "(&(objectCategory=person)(sAMAccountName=$LOGIN))";
     		   					$sr = ldap_search($this->ldapconn, $dc, $filter, $attributes) or die ("Error in search query: ".
															dap_error($ldapconn).PHP_EOL);
     							$entries = ldap_get_entries($this->ldapconn, $sr);
							$map = array(
									"login"		=> $entries[0]["samaccountname"][0],
									"displayname"	=> \UTILS\sozdat_slag( $entries[0]["displayname"][0]),
									"givenname"	=> \UTILS\sozdat_slag($entries[0]["givenname"][0]),
									"mail"		=> $entries[0]["mail"][0],
									"mobile"	=> $entries[0]["ipphone"][0],
									"idNumber"	=> $uId
								);
							$this->init($TPLprefix, $map);
        		    				//print("---------------------------------".PHP_EOL);
							$uId ++;
							printf(" %s is DONE ".PHP_EOL, $map["login"] );
						}
    					}
				}
	        	} catch (\Throwable $e) {
				$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
               			$errmsg = sprintf("%s DUMP:%s. \n ERROR: %s\n",$errpoint, var_export($map, true),$e->getMessage() );
                		throw new \ERROR\LdapThrowable( $errmsg );
        		}
		}
	}
}

namespace {
	try {

		set_error_handler( function ($errno, $errstr, $errfile, $errline): bool {
        		if (!(error_reporting() & $errno)) {
                		return false;
        		}
	        	$errmsg =  sprintf( PHP_EOL."Handler captured error number:%s, ERROR:'%s', FILE:'%s', LINEL:'%s'".PHP_EOL ,
							 $errno,$errstr, $errfile ,$errline);
        		throw new \ERROR\LdapThrowable( $errmsg);
			return true;
		});

		$ldaprdn  = 'ROOTDN-ACCOUNT';
		$ldappass = 'ROOTDN-PASSWORD';
		$ldapserver = "ldap://x.x.x.x";

		$dc = "OU=D,OU=W,DC=X,DC=Y,DC=Z";

		$LOGINS = array (
        	           		'user1',
                	   		'user2',
                        		'user3'
                         		);

		\CONVERTOR\GENERATOR::getInstance()->main(2100,"TPL",$ldaprdn,$ldappass,$ldapserver,$dc,$LOGINS);

	} catch (\Throwable $e) {
		$errpoint = \UTILS\error_point( __NAMESPACE__, __FUNCTION__, __METHOD__, __DIR__, __FILE__, __LINE__ );
        	$errmsg = sprintf("\n--------------------------------------------------------------------\n %s DUMP:%s. \n ERROR: %s\n",
				$errpoint, var_export($map, true),$e->getMessage() );
        	print ( $errmsg );
	}
}

?>
