<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Graylog;

use RuntimeException;
use Icinga\data\Paginatable;
use Icinga\Data\Queryable;
use Icinga\Util\Json;
use Icinga\Web\Url;
use iplx\Http\Client;
use iplx\Http\Request;
use iplx\Http\Uri;

/* TODO
http://192.168.33.6:9000/api/api-browser#!/Search/Absolute

https://github.com/blue-yonder/bonfire/blob/master/bonfire/graylog_api.py

http://annaken.github.io/using-graylogs-rest-api

curl -k -u admin:admin -H 'Accept: application/json' 'http://192.168.33.6:9000/api/search/universal/relative?range=3000&fields=source&query=source:icinga2'

curl -k -u admin:admin -H 'Accept: application/json' 'http://192.168.33.6:9000/api/search/universal/relative?range=3000&query=source:icinga2'

{"query":"source:icinga2","built_query":"{\n  \"from\" : 0,\n  \"size\" : 150,\n  \"query\" : {\n    \"bool\" : {\n      \"must\" : {\n        \"query_string\" : {\n          \"query\" : \"source:icinga2\",\n          \"allow_leading_wildcard\" : false\n        }\n      },\n      \"filter\" : {\n        \"bool\" : {\n          \"must\" : {\n            \"range\" : {\n              \"timestamp\" : {\n                \"from\" : \"2017-11-05 15:13:45.420\",\n                \"to\" : \"2017-11-05 16:03:45.422\",\n                \"include_lower\" : true,\n                \"include_upper\" : true\n              }\n            }\n          }\n        }\n      }\n    }\n  },\n  \"sort\" : [ {\n    \"timestamp\" : {\n      \"order\" : \"desc\"\n    }\n  } ]\n}","used_indices":[{"index_name":"graylog_0","begin":"1970-01-01T00:00:00.000Z","end":"1970-01-01T00:00:00.000Z","calculated_at":"2017-11-05T12:22:29.751Z","took_ms":0}],"messages":[{"highlight_ranges":{},"message":{"service_state":"CRITICAL","last_state":1.0,"gl2_remote_ip":"127.0.0.1","max_check_attempts":3.0,"service_name":"random-003","latency":0.009143829345703125,"gl2_remote_port":51976,"streams":["000000000000000000000001"],"source":"icinga2","type":"CHECK RESULT","gl2_source_input":"59ff02b001689a5a51131a18","message":"Hello from icinga2-graylog","execution_time":1.010894775390625E-4,"current_check_attempt":1.0,"hostname":"graylog-host","check_command":"random","full_message":"Hello from icinga2-graylog","last_hard_state":2.0,"check_source":"icinga2-graylog","gl2_source_node":"10f90dec-290e-46ef-a306-8d85487b8b84","_id":"e43fdc70-c242-11e7-9b37-001c427d562f","state":"CRITICAL","timestamp":"2017-11-05T16:03:41.109Z"},"index":"graylog_0","decoration_stats":null},{"highlight_ranges":{},"message":{"service_state":"CRITICAL","last_state":1.0,"max_check_attempts":3.0,"service_name":"random-003","gl2_remote_ip":"127.0.0.1","gl2_remote_port":51976,"streams":["000000000000000000000001"],"source":"icinga2","message":"Hello from icinga2-graylog","type":"STATE CHANGE","gl2_source_input":"59ff02b001689a5a51131a18","current_check_attempt":1.0,"hostname":"graylog-host","check_command":"random","full_message":"Hello from icinga2-graylog","last_hard_state":2.0,"check_source":"icinga2-graylog","gl2_source_node":"10f90dec-290e-46ef-a306-8d85487b8b84","_id":"e4402a90-c242-11e7-9b37-001c427d562f","state":"CRITICAL","timestamp":"2017-11-05T16:03:41.109Z"},"index":"graylog_0","decoration_stats":null},

*/

class Query implements Queryable, Paginatable
{
    const MAX_RESULT_WINDOW = 10000;

    protected $graylog;
    protected $fields;
    protected $filter;
    protected $index; //TODO
    protected $limit;
    protected $offset;
    protected $response;
    protected $patch = [];

    public function __construct(Graylog $graylog, array $fields = [])
    {
        $this->graylog = $graylog;

        $this->fields = $fields;
    }

    public function from($target, array $fields = null)
    {
        $this->index = $target;

        if (! empty($fields)) {
            $this->fields = $fields;
        }

        return $this;
    }

    public function limit($count = null, $offset = null)
    {
        $this->limit = $count;
        $this->offset = $offset;
    }

    public function hasLimit()
    {
        return $this->limit !== null;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function hasOffset()
    {
        return $this->offset !== null;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function count()
    {
        $this->execute();

        $total = $this->response['size'];
        if ($total > self::MAX_RESULT_WINDOW) {
            return self::MAX_RESULT_WINDOW;
        }

        return $total;
    }

    public function filter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function execute()
    {
        if ($this->response === null) {
            $config = $this->graylog->getConfig();

            $client = new Client();

            //curl -k -u admin:admin -H 'Accept: application/json' 'http://192.168.33.6:9000/api/search/universal/relative?query=hostname:graylog-host'

            //TODO: add stream id to query (this can be done with the filter param). https://github.com/blue-yonder/bonfire/blob/master/bonfire/cli.py#L175

            /* Graylog expects everything passed as URL parameters */



            //http://useof.org/java-open-source/javax.ws.rs.core.SecurityContext
            $headers = [
                'User-Agent'    => 'icingaweb2-module-graylog',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("$config->user:$config->password")
            ];

            //Graylog nrequires params for /search/universal/relative
            // - query=hostname:graylog-host
            // - fields=hostname,source

            $params = array_filter(array_merge([
                'fields'    => join(",", array_merge(['timestamp'], $this->fields)), //include the timestamp, and format fields. Graylog does not understand URL array[] notation
                'query'     => $this->filter,
                'offset'    => $this->getOffset(),
                'limit'     => $this->getLimit(),
                'sort'      => 'timestamp:desc' //Graylog specific format
            ], $this->patch), function($part) { return $part !== null; });


            $url = Url::fromPath("{$config->uri}/search/universal/relative") //TODO: Evaluate whether relative or absolute is needed here
                ->setParams($params)
                ->getAbsoluteUrl();


            /*
             * This requires PHP 5.6+!
             */
            $uri = (new Uri($url))
                ->withUserInfo($config->user, $config->password);

            print "Executing URL $uri with params " . json_encode($params);

            $request = new Request(
                'GET',
                $uri,
                $headers,
                null
            );

            //var_dump($request);
            //print "---------------------------\n\n";

            $response = $client->send($request);

            //var_dump((string)$response->getBody());

            $responseBody = Json::decode((string) $response->getBody(), true);

            //var_dump($responseBody);

            //die();

            //Graylog has this type of error handling
            if (isset($responseBody['type']) && $responseBody['type'] === 'ApiError') {
                throw new RuntimeException(
                    'Got error from Graylog: ' . $responseBody['message']
                );
            }

            //Generic iplx/http error handling
            if (isset($responseBody['error'])) {
                throw new RuntimeException(
                    'Got error from Graylog: '. $responseBody['error']['type'] . ': ' . $responseBody['error']['reason']
                );
            }

            $this->response = $responseBody;

            //"timestamp":"2017-11-05T16:05:31.623Z"

            /* curl -k -u admin:admin -H 'Accep/api/search/universal/relative?range=3000&query=source:icinga2&fields=check_command'
             *
             * "message":{"check_command":"random","_id":"22178c33-c245-11e7-9b37-001c427d562f","timestamp":"2017-11-05T16:19:43.858Z"}
             */
            print ".........\n\n";
        }

    }

    public function getFields()
    {
        $this->execute();

        $events = $this->response['messages'];

        //var_dump(json_encode($events));
        //die();

        $fields = [];

        if (! empty($events)) {
            $event = reset($events);

            Graylog::extractFields($event['message'], $fields);
        }

        //var_dump($fields);
        //die();
        return $fields;
    }

    public function fetchAll()
    {
        $this->execute();

        //print ".........\n\n";
        //var_dump($this->response);
        //die();

        return $this->response['messages'];
    }

    public function patch(array $patch)
    {
        $this->patch = $patch;

        return $this;
    }

    public function getResponse()
    {
        $this->execute();

        return $this->response;
    }
}

