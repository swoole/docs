# 오류 보고서 제출


## 알림

Swoole 커널의 버그를 발견하였을 경우, 보고서를 제출하시기 바랍니다. Swoole의 커널 개발자들은 아직 이 문제를 모르고 계실 수 있습니다. 당신이 직접 보고서를 제출하지 않는 이상, 버그는 발견되고 수정되기 어려울 수 있습니다. [GitHub의 이슈 구역](https://github.com/swoole/swoole-src/issues)에서 오류 보고서를 제출하실 수 있으며, 여기서 제출한 오류 보고서는 가장 우선으로 해결됩니다.

이메일 리스트나 개인 이메일로 오류 보고서를 보내지 마십시오. GitHub의 이슈 구역에서 Swoole에 대한 어떠한 요구나 제안도 할 수 있습니다.

오류 보고서를 제출하기 전에, 아래의 **오류 보고서 제출 방법**을 미리 읽어 주십시오.


## 새 이슈 생성

이슈를 생성할 때, 시스템은 다음과 같은 템플릿을 제공하므로, 세심하게 작성하시기 바랍니다. 그렇지 않으면 정보가 부족하여 이슈가 무시될 수 있습니다:

```markdown

Please answer these questions before submitting your issue. Thanks!
> 이슈를 제출하기 전에 다음 질문에 답하십시오:
	
1. What did you do? If possible, provide a simple script for reproducing the error.
> 문제 발생 과정에 대해 자세히 설명하고, 관련 코드를 제공합니다. 안정적으로 재현할 수 있는 간단한 스크립트 코드를 제공하는 것이 가장 좋습니다.

2. What did you expect to see?
> 기대한 결과는 무엇입니까?

3. What did you see instead?
> 실제로 실행된 결과는 무엇입니까?

4. What version of Swoole are you using (`php --ri swoole`)?
> 당신이 사용하는 Swoole 버전은 무엇입니까? `php --ri swoole` 명령어로 출력된 내용을 제공합니다.	

5. What is your machine environment used (including the version of kernel & php & gcc)?
> 당신이 사용하는 기계 시스템 환경은 무엇입니까(커널, PHP, gcc 컴파일러 버전 정보 포함)?	
> `uname -a`, `php -v`, `gcc -v` 명령어로 출력할 수 있습니다.

```

그중에서 가장 중요한 것은 **안정적으로 재현할 수 있는 간단한 스크립트 코드**를 제공하는 것입니다. 그렇지 않으면 가능한 한 많은 다른 정보를 제공하여 개발자가 오류 원인을 판단하도록 돕습니다.


## 메모리 분석 (강력히 추천)

더 많이, Valgrind은 gdb보다 메모리 문제를 발견하는 데 더 효과적입니다. 다음 명령어로 당신의 프로그램을 실행하여 버그를 트리거하십시오:

```shell
USE_ZEND_ALLOC=0 valgrind --log-file=/tmp/valgrind.log php your_file.php
```

* 프로그램이 오류发生时, `ctrl+c`를 입력하여 종료하고, `/tmp/valgrind.log` 파일을 업로드하여 개발팀이 버그를 위치시키도록 합니다.

## 세그멘탈 에러(핵심 디스첩트)에 대한 정보

또한, 특정 상황에서 개발자를 돕기 위해 디버거 도구를 사용할 수 있습니다.

```shell
WARNING	swManager_check_exit_status: worker#1 abnormal exit, status=0, signal=11
```

Swoole 로그에 위와 같은 메시지가 나타났을 때(signal11), 프로그램이 `핵심 디스첩트`가 발생했다는 것을 의미합니다. 이를 위해 트레이스 디버거 도구를 사용하여 발생 위치를 확인해야 합니다.

> `swoole`를 트레이스하기 전에 `gdb`를 사용하려면, 컴파일 시 `--enable-debug` 매개변수를 추가하여 더 많은 정보를 유지해야 합니다.

핵심 디스첩트 파일을 생성합니다.
```shell
ulimit -c unlimited
```

버그를 트리거하면, 핵심 디스첩트 파일은 프로그램 디렉터리 또는 시스템 루트 디렉터리 또는 `/cores` 디렉터리 아래 생성됩니다(시스템 구성이 달라질 수 있음).

다음 명령어로 gdb로 프로그램을 디버그합니다.

```
gdb php core
gdb php /tmp/core.1234
```

그 다음 `bt`를 입력하고 Enter를 누르면, 문제가 발생한 호출 스택을 볼 수 있습니다.
```
(gdb) bt
```

특정 호출 스택 프레임을 확인하려면 `f 숫자` 명령어를 입력합니다.
```
(gdb) f 1
(gdb) f 0
```

위의 모든 정보를 이슈에 붙여주십시오.
