<?php
/**
 * Created by PhpStorm.
 * User: ShengYue
 * Date: 2016/7/6
 * Time: 18:57
 */
//
//$data='                    </Names>
//                        <Odds>
//                          <Book id="9">
//                            <CO>
//12.00
//                            </CO>
//                          </Book>
//                        </Odds>
//                      </Competitor>
//                    </Competitors>
//                  </Outright>
//                  <Outright>
//                    <OID>
//133429
//                    </OID>
//                    <StartDate type="start">
//2016-06-30 13:00:00
//                    </StartDate>
//                    <Names>
//                      <Name lang="en">
//WGC Bridgestone Invitational - 1st Round Leader
//                      </Name>
//                    </Names>
//                    <Competitors total="61">
//                      <Competitor>
//                        <Names>
//                          <Name lang="en">
//Scott, Adam
//                          </Name>
//                        </Names>
//                        <Odds>
//                          <Book id="9">
//                            <CO>
//34.00
//                            </CO>
//                          </Book>
//                        </Odds>
//                      </Competitor>
//                      <Competitor>
//                        <Names>
//                          <Name lang="en">
//Rose, Justin
//                          </Name>
//                        </Names>
//                        <Odds>
//                          <Book id="9">
//                            <CO>
//34.00
//                            </CO>
//                          </Book>
//                        </Odds>
//                      </Competitor>
//                    </Competitors>
//                  </Outright>
//                </Outrights>
//              </Category>
//            </Categories>
//          </Sport>
//        </Sports>
//      </OddsData>
//    </OddsFeed>
//';
$postStr = file_get_contents("http://interface.win007.com/zq/today.aspx");
$obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$data = json_decode(json_encode($obj), true);
print_r($data);