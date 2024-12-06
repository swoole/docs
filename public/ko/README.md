# 스와일로우

?> `스와일로우`는 `C++` 언어로 작성된 비동기 사건 주도 및 코루틴 기반의 병렬 네트워크 통신 엔진으로, `PHP`에 [코루틴](/coroutine)、[고성능](/question/use?id=how-is-the-performance-of-swoole) 네트워크 프로그래밍 지원을 제공합니다. 다양한 통신プロト콜을 지원하는 네트워크 서버와 클라이언트 모듈을 제공하여 `TCP/UDP 서비스`、`고성능 웹`、`WebSocket 서비스`、`인공지능 IoT`、`실시간 커뮤니케이션`、`게임`、`미세 서비스` 등을 편리하고 빠르게 구현할 수 있어, `PHP`가 전통적인 웹 분야에만 국한되지 않게 합니다.


## 스와일로우 클래스 다이어그램

!> 직접 링크를 클릭하여 해당 문서 페이지로 이동할 수 있습니다.

[//]: # (https://naotu.baidu.com/file/bd9d2ba7dfae326e6976f0c53f88b18c)

<embed src="/_images/swoole_class.svg" type="image/svg+xml" alt="스와일로우 아키텍처 다이어그램" />


## 공식 웹사이트

* [스와일로우 공식 웹사이트](//www.swoole.com)
* [비즈니스 제품 및 지원](//business.swoole.com)
* [스와일로우 질문](//wenda.swoole.com)


## 프로젝트 주소

* [GitHub](//github.com/swoole/swoole-src) **（支援은 별도로 스타를 눌러주세요）**
* [码云](//gitee.com/swoole/swoole)
* [PECL](//pecl.php.net/package/swoole)


## 개발 도구

* [IDE Helper](https://github.com/swoole/ide-helper)
* [Yasd](https://github.com/swoole/yasd)
* [디버거](https://github.com/swoole/debugger)


## 저작권 정보

본 문서는 원래의 구 버전 스와일로우 문서 내용을 기반으로 작성되었으며, 여러분이 항상 불평해온 문서 문제를 해결하기 위해 모집되었습니다. 현대적인 문서 조직 형식을 채택하여 `Swoole4`의 내용만 포함하고 있으며, 많은 구 문서의 오류 사항을 수정하고 문서 세부 사항을 최적화하며, 예제 코드와 일부 교육 내용을 추가하여 `Swoole` 초보자에게 더욱 친근하게 만들었습니다.

본 문서의 모든 내용, 텍스트, 이미지 및 오디오 비디오 자료의 저작권은 모두 **상하이 시와 우 네트워크 기술 유한 회사**에 속합니다. 어떠한 미디어, 웹사이트 또는 개인도 외부 링크 형식으로 인용할 수 있지만, 계약 위임 없이 어떤 형태로도 복제하거나 발표해서는 안 됩니다.


## 문서 발행자

* 양재 [GitHub](https://github.com/TTSimple)
* 국신화 [Weibo](https://www.weibo.com/u/2661945152)
* [루페이](https://github.com/sy-records) [Weibo](https://weibo.com/5384435686)


## 문제 피드백

본 문서의 내용 문제(예: 철자 오류, 예제 오류, 내용 누락 등) 및 요구 제안에 대해서는 [swoole-inc/report](https://github.com/swoole-inc/report) 프로젝트에 `issue`를 제출하거나, 우측 상단의 [피드백](/?id=main)을 클릭하여 `issue` 페이지로 직접跳转할 수 있습니다.

수용되면 제출자의 정보를 [문서 기여자](/CONTRIBUTING) 목록에 추가하여 감사의 뜻을 표합니다.

## 문서 원칙

직설적인 언어를 사용하고, **최대한** `Swoole`의 하단 기술 세부 사항과 일부 하단 개념을 소개하지 않으며, 하단의 나중에는 별도의 `hack` 장을 유지할 수 있습니다;

어떤 개념을 돌파할 수 없을 때, **반드시** 이 개념을 소개하는 중앙 집중식의 장이 있어야 하며, 다른 곳에서는 내부 링크를 통해跳转합니다. 예: [사건 루프](/learn?id=무엇이eventloop인가) ;

문서를 작성할 때는 사고방식을 전환하고, 초보자의 입장에서 다른 사람들이 이해할 수 있을지 여부를 검토해야 합니다;

기능 변경이 발생할 때 **반드시** 관련 모든 부분을 수정해야 하며, 단지 한 곳만 수정해서는 안 됩니다;

각 기능 모듈은 **반드시** 완전한 예제가 있어야 합니다;
