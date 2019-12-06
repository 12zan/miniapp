<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class ProjectUrlMatcher extends Symfony\Component\Routing\Tests\Fixtures\RedirectableUrlMatcher
{
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = $allowSchemes = array();
        if ($ret = $this->doMatch($pathinfo, $allow, $allowSchemes)) {
            return $ret;
        }
        if ($allow) {
            throw new MethodNotAllowedException(array_keys($allow));
        }
        if (!in_array($this->context->getMethod(), array('HEAD', 'GET'), true)) {
            // no-op
        } elseif ($allowSchemes) {
            redirect_scheme:
            $scheme = $this->context->getScheme();
            $this->context->setScheme(key($allowSchemes));
            try {
                if ($ret = $this->doMatch($pathinfo)) {
                    return $this->redirect($pathinfo, $ret['_route'], $this->context->getScheme()) + $ret;
                }
            } finally {
                $this->context->setScheme($scheme);
            }
        } elseif ('/' !== $pathinfo) {
            $pathinfo = '/' !== $pathinfo[-1] ? $pathinfo.'/' : substr($pathinfo, 0, -1);
            if ($ret = $this->doMatch($pathinfo, $allow, $allowSchemes)) {
                return $this->redirect($pathinfo, $ret['_route']) + $ret;
            }
            if ($allowSchemes) {
                goto redirect_scheme;
            }
        }

        throw new ResourceNotFoundException();
    }

    private function doMatch(string $rawPathinfo, array &$allow = array(), array &$allowSchemes = array()): ?array
    {
        $allow = $allowSchemes = array();
        $pathinfo = rawurldecode($rawPathinfo);
        $context = $this->context;
        $requestMethod = $canonicalMethod = $context->getMethod();
        $host = strtolower($context->getHost());

        if ('HEAD' === $requestMethod) {
            $canonicalMethod = 'GET';
        }

        switch ($pathinfo) {
            default:
                $routes = array(
                    '/test/baz' => array(array('_route' => 'baz'), null, null, null),
                    '/test/baz.html' => array(array('_route' => 'baz2'), null, null, null),
                    '/test/baz3/' => array(array('_route' => 'baz3'), null, null, null),
                    '/foofoo' => array(array('_route' => 'foofoo', 'def' => 'test'), null, null, null),
                    '/spa ce' => array(array('_route' => 'space'), null, null, null),
                    '/multi/new' => array(array('_route' => 'overridden2'), null, null, null),
                    '/multi/hey/' => array(array('_route' => 'hey'), null, null, null),
                    '/ababa' => array(array('_route' => 'ababa'), null, null, null),
                    '/route1' => array(array('_route' => 'route1'), 'a.example.com', null, null),
                    '/c2/route2' => array(array('_route' => 'route2'), 'a.example.com', null, null),
                    '/route4' => array(array('_route' => 'route4'), 'a.example.com', null, null),
                    '/c2/route3' => array(array('_route' => 'route3'), 'b.example.com', null, null),
                    '/route5' => array(array('_route' => 'route5'), 'c.example.com', null, null),
                    '/route6' => array(array('_route' => 'route6'), null, null, null),
                    '/route11' => array(array('_route' => 'route11'), '#^(?P<var1>[^\\.]++)\\.example\\.com$#sDi', null, null),
                    '/route12' => array(array('_route' => 'route12', 'var1' => 'val'), '#^(?P<var1>[^\\.]++)\\.example\\.com$#sDi', null, null),
                    '/route17' => array(array('_route' => 'route17'), null, null, null),
                    '/secure' => array(array('_route' => 'secure'), null, null, array('https' => 0)),
                    '/nonsecure' => array(array('_route' => 'nonsecure'), null, null, array('http' => 0)),
                );

                if (!isset($routes[$pathinfo])) {
                    break;
                }
                list($ret, $requiredHost, $requiredMethods, $requiredSchemes) = $routes[$pathinfo];

                if ($requiredHost) {
                    if ('#' !== $requiredHost[0] ? $requiredHost !== $host : !preg_match($requiredHost, $host, $hostMatches)) {
                        break;
                    }
                    if ('#' === $requiredHost[0] && $hostMatches) {
                        $hostMatches['_route'] = $ret['_route'];
                        $ret = $this->mergeDefaults($hostMatches, $ret);
                    }
                }

                $hasRequiredScheme = !$requiredSchemes || isset($requiredSchemes[$context->getScheme()]);
                if ($requiredMethods && !isset($requiredMethods[$canonicalMethod]) && !isset($requiredMethods[$requestMethod])) {
                    if ($hasRequiredScheme) {
                        $allow += $requiredMethods;
                    }
                    break;
                }
                if (!$hasRequiredScheme) {
                    $allowSchemes += $requiredSchemes;
                    break;
                }

                return $ret;
        }

        $matchedPathinfo = $host.'.'.$pathinfo;
        $regexList = array(
            0 => '{^(?'
                .'|(?:(?:[^.]*+\\.)++)(?'
                    .'|/foo/(baz|symfony)(*:46)'
                    .'|/bar(?'
                        .'|/([^/]++)(*:69)'
                        .'|head/([^/]++)(*:89)'
                    .')'
                    .'|/test/([^/]++)/(?'
                        .'|(*:115)'
                    .')'
                    .'|/([\']+)(*:131)'
                    .'|/a/(?'
                        .'|b\'b/([^/]++)(?'
                            .'|(*:160)'
                            .'|(*:168)'
                        .')'
                        .'|(.*)(*:181)'
                        .'|b\'b/([^/]++)(?'
                            .'|(*:204)'
                            .'|(*:212)'
                        .')'
                    .')'
                    .'|/multi/hello(?:/([^/]++))?(*:248)'
                    .'|/([^/]++)/b/([^/]++)(?'
                        .'|(*:279)'
                        .'|(*:287)'
                    .')'
                    .'|/aba/([^/]++)(*:309)'
                .')|(?i:([^\\.]++)\\.example\\.com)\\.(?'
                    .'|/route1(?'
                        .'|3/([^/]++)(*:371)'
                        .'|4/([^/]++)(*:389)'
                    .')'
                .')|(?i:c\\.example\\.com)\\.(?'
                    .'|/route15/([^/]++)(*:441)'
                .')|(?:(?:[^.]*+\\.)++)(?'
                    .'|/route16/([^/]++)(*:488)'
                    .'|/a/(?'
                        .'|a\\.\\.\\.(*:509)'
                        .'|b/(?'
                            .'|([^/]++)(*:530)'
                            .'|c/([^/]++)(*:548)'
                        .')'
                    .')'
                .')'
                .')$}sD',
        );

        foreach ($regexList as $offset => $regex) {
            while (preg_match($regex, $matchedPathinfo, $matches)) {
                switch ($m = (int) $matches['MARK']) {
                    case 115:
                        $matches = array('foo' => $matches[1] ?? null);

                        // baz4
                        return $this->mergeDefaults(array('_route' => 'baz4') + $matches, array());

                        // baz5
                        $ret = $this->mergeDefaults(array('_route' => 'baz5') + $matches, array());
                        if (!isset(($a = array('POST' => 0))[$requestMethod])) {
                            $allow += $a;
                            goto not_baz5;
                        }

                        return $ret;
                        not_baz5:

                        // baz.baz6
                        $ret = $this->mergeDefaults(array('_route' => 'baz.baz6') + $matches, array());
                        if (!isset(($a = array('PUT' => 0))[$requestMethod])) {
                            $allow += $a;
                            goto not_bazbaz6;
                        }

                        return $ret;
                        not_bazbaz6:

                        break;
                    case 160:
                        $matches = array('foo' => $matches[1] ?? null);

                        // foo1
                        $ret = $this->mergeDefaults(array('_route' => 'foo1') + $matches, array());
                        if (!isset(($a = array('PUT' => 0))[$requestMethod])) {
                            $allow += $a;
                            goto not_foo1;
                        }

                        return $ret;
                        not_foo1:

                        break;
                    case 204:
                        $matches = array('foo1' => $matches[1] ?? null);

                        // foo2
                        return $this->mergeDefaults(array('_route' => 'foo2') + $matches, array());

                        break;
                    case 279:
                        $matches = array('_locale' => $matches[1] ?? null, 'foo' => $matches[2] ?? null);

                        // foo3
                        return $this->mergeDefaults(array('_route' => 'foo3') + $matches, array());

                        break;
                    default:
                        $routes = array(
                            46 => array(array('_route' => 'foo', 'def' => 'test'), array('bar'), null, null),
                            69 => array(array('_route' => 'bar'), array('foo'), array('GET' => 0, 'HEAD' => 1), null),
                            89 => array(array('_route' => 'barhead'), array('foo'), array('GET' => 0), null),
                            131 => array(array('_route' => 'quoter'), array('quoter'), null, null),
                            168 => array(array('_route' => 'bar1'), array('bar'), null, null),
                            181 => array(array('_route' => 'overridden'), array('var'), null, null),
                            212 => array(array('_route' => 'bar2'), array('bar1'), null, null),
                            248 => array(array('_route' => 'helloWorld', 'who' => 'World!'), array('who'), null, null),
                            287 => array(array('_route' => 'bar3'), array('_locale', 'bar'), null, null),
                            309 => array(array('_route' => 'foo4'), array('foo'), null, null),
                            371 => array(array('_route' => 'route13'), array('var1', 'name'), null, null),
                            389 => array(array('_route' => 'route14', 'var1' => 'val'), array('var1', 'name'), null, null),
                            441 => array(array('_route' => 'route15'), array('name'), null, null),
                            488 => array(array('_route' => 'route16', 'var1' => 'val'), array('name'), null, null),
                            509 => array(array('_route' => 'a'), array(), null, null),
                            530 => array(array('_route' => 'b'), array('var'), null, null),
                            548 => array(array('_route' => 'c'), array('var'), null, null),
                        );

                        list($ret, $vars, $requiredMethods, $requiredSchemes) = $routes[$m];

                        foreach ($vars as $i => $v) {
                            if (isset($matches[1 + $i])) {
                                $ret[$v] = $matches[1 + $i];
                            }
                        }

                        $hasRequiredScheme = !$requiredSchemes || isset($requiredSchemes[$context->getScheme()]);
                        if ($requiredMethods && !isset($requiredMethods[$canonicalMethod]) && !isset($requiredMethods[$requestMethod])) {
                            if ($hasRequiredScheme) {
                                $allow += $requiredMethods;
                            }
                            break;
                        }
                        if (!$hasRequiredScheme) {
                            $allowSchemes += $requiredSchemes;
                            break;
                        }

                        return $ret;
                }

                if (548 === $m) {
                    break;
                }
                $regex = substr_replace($regex, 'F', $m - $offset, 1 + strlen($m));
                $offset += strlen($m);
            }
        }
        if ('/' === $pathinfo && !$allow) {
            throw new Symfony\Component\Routing\Exception\NoConfigurationException();
        }

        return null;
    }
}
