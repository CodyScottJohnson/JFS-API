<?php
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Schema;
$recruitType = new ObjectType([
    'name' => 'Recruit',
    'description' => 'Any one currently being worked with and recruited',
    'fields' => [
        'INDV_ID' => [
            'type' => Type::nonNull(Type::int()),
            'description' => 'The id of the human.',
        ],
        'FNAME' => [
            'type' => Type::string(),
            'description' => 'The name of the human.',
            'resolve' => function () {

                return 'Cody';
            },
        ],
      
    ]
]);
function getRecruit($id){
  echo $dsn;
  $pdo = new PDO($dsn.'JFS_v1', $username, $password);
  $db = new NotORM($pdo);
  $recruit = $db->RecruitInfo->select('Info')->where('RecruitID', $args['id']);
  return array("INDV_ID"=>1);
} 
$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'recruits' => [
            'type' => $recruitType,
            'args' => [
                'id' => [
                    'description' => 'If omitted, returns the hero of the whole saga. If provided, returns the hero of that particular episode.',
                    'type' => Type::int()
                ]
            ],
            'resolve' => function ($root, $args) {
               // $recruit = $db->RecruitInfo->select('Info')->where('RecruitID', $args['id']);
                $recruit = array("INDV_ID"=>1);
                return getRecruit($id);
            },
        ]
 
    ]
]);

// TODOC
$mutationType = null;

$schema = new Schema([
    'query' => $queryType, 
    'mutation' => $mutationType,
    
]);
?>