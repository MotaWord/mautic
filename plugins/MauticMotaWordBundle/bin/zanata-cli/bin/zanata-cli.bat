@REM Copyright 2013-2015, Red Hat, Inc.
@REM
@REM This is free software; you can redistribute it and/or modify it under the
@REM terms of the GNU Lesser General Public License as published by the Free
@REM Software Foundation; either version 2.1 of the License, or (at your option)
@REM any later version.
@REM
@REM This software is distributed in the hope that it will be useful, but WITHOUT
@REM ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
@REM FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
@REM details.
@REM
@REM You should have received a copy of the GNU Lesser General Public License
@REM along with this software; if not, write to the Free Software Foundation,
@REM Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA, or see the FSF
@REM site: http://www.fsf.org.

@echo off

set ERROR_CODE=0

:init
@REM Decide how to startup depending on the version of windows

@REM -- Win98ME
if NOT "%OS%"=="Windows_NT" goto Win9xArg

@REM set local scope for the variables with windows NT shell
if "%OS%"=="Windows_NT" @setlocal

@REM -- 4NT shell
if "%eval[2+2]" == "4" goto 4NTArgs

@REM -- Regular WinNT shell
set CMD_LINE_ARGS=%*
goto WinNTGetScriptDir

@REM The 4NT Shell from jp software
:4NTArgs
set CMD_LINE_ARGS=%$
goto WinNTGetScriptDir

:Win9xArg
@REM Slurp the command line arguments.  This loop allows for an unlimited number
@REM of arguments (up to the command line limit, anyway).
set CMD_LINE_ARGS=
:Win9xApp
if %1a==a goto Win9xGetScriptDir
set CMD_LINE_ARGS=%CMD_LINE_ARGS% %1
shift
goto Win9xApp

:Win9xGetScriptDir
set SAVEDIR=%CD%
%0\
cd %0\..\.. 
set BASEDIR=%CD%
cd %SAVEDIR%
set SAVE_DIR=
goto repoSetup

:WinNTGetScriptDir
set BASEDIR=%~dp0\..

:repoSetup
set REPO=


if "%JAVACMD%"=="" set JAVACMD=java

if "%REPO%"=="" set REPO=%BASEDIR%\lib

set CLASSPATH="%BASEDIR%"\etc;"%REPO%"\args4j-2.32.jar;"%REPO%"\commons-io-2.4.jar;"%REPO%"\slf4j-api-1.7.22.jar;"%REPO%"\slf4j-log4j12-1.7.22.jar;"%REPO%"\log4j-1.2.17.jar;"%REPO%"\zanata-client-commands-4.5.0-SNAPSHOT.jar;"%REPO%"\zanata-common-api-4.5.0-SNAPSHOT.jar;"%REPO%"\enunciate-core-annotations-2.9.1.jar;"%REPO%"\hibernate-validator-5.2.3.Final.jar;"%REPO%"\jboss-logging-3.3.0.Final.jar;"%REPO%"\classmate-1.1.0.jar;"%REPO%"\validation-api-1.1.0.Final.jar;"%REPO%"\resteasy-multipart-provider-3.0.13.Final.jar;"%REPO%"\resteasy-jaxrs-3.0.13.Final.jar;"%REPO%"\mail-1.5.0-b01.jar;"%REPO%"\activation-1.1.1.jar;"%REPO%"\apache-mime4j-0.6.jar;"%REPO%"\jackson-core-asl-1.9.13.jar;"%REPO%"\jackson-mapper-asl-1.9.13.jar;"%REPO%"\stax-api-1.0.jar;"%REPO%"\jboss-jaxrs-api_2.0_spec-1.0.0.Final.jar;"%REPO%"\zanata-adapter-properties-4.5.0-SNAPSHOT.jar;"%REPO%"\zanata-adapter-po-4.5.0-SNAPSHOT.jar;"%REPO%"\jgettext-0.15.jar;"%REPO%"\antlr-2.7.7.jar;"%REPO%"\zanata-adapter-xliff-4.5.0-SNAPSHOT.jar;"%REPO%"\txw2-2.2.11.jar;"%REPO%"\zanata-rest-client-4.5.0-SNAPSHOT.jar;"%REPO%"\resteasy-client-3.0.13.Final.jar;"%REPO%"\jboss-annotations-api_1.2_spec-1.0.0.Final.jar;"%REPO%"\resteasy-jackson-provider-3.0.13.Final.jar;"%REPO%"\resteasy-jaxb-provider-3.0.13.Final.jar;"%REPO%"\jackson-jaxrs-1.9.13.jar;"%REPO%"\jackson-xc-1.9.13.jar;"%REPO%"\commons-beanutils-1.9.3.jar;"%REPO%"\commons-collections-3.2.2.jar;"%REPO%"\maven-artifact-3.0.4.jar;"%REPO%"\plexus-utils-2.0.6.jar;"%REPO%"\zanata-adapter-glossary-4.5.0-SNAPSHOT.jar;"%REPO%"\commons-configuration-1.10.jar;"%REPO%"\commons-lang-2.6.jar;"%REPO%"\openprops-0.7.1.jar;"%REPO%"\joda-time-2.2.jar;"%REPO%"\commons-lang3-3.6.jar;"%REPO%"\commons-codec-1.10.jar;"%REPO%"\jaxb-api-2.2.12.jar;"%REPO%"\commons-csv-1.2.jar;"%REPO%"\ant-1.8.2.jar;"%REPO%"\ant-launcher-1.8.2.jar;"%REPO%"\commons-exec-1.1.jar;"%REPO%"\httpclient-4.4.1.jar;"%REPO%"\httpcore-4.4.1.jar;"%REPO%"\jansi-1.11.jar;"%REPO%"\zanata-common-util-4.5.0-SNAPSHOT.jar;"%REPO%"\xom-1.2.10.jar;"%REPO%"\xalan-2.7.0.jar;"%REPO%"\guava-18.0.jar;"%REPO%"\jcl-over-slf4j-1.7.22.jar;"%REPO%"\zanata-cli-4.5.0-SNAPSHOT.jar

set ENDORSED_DIR=
if NOT "%ENDORSED_DIR%" == "" set CLASSPATH="%BASEDIR%"\%ENDORSED_DIR%\*;%CLASSPATH%

if NOT "%CLASSPATH_PREFIX%" == "" set CLASSPATH=%CLASSPATH_PREFIX%;%CLASSPATH%

@REM Reaching here means variables are defined and arguments have been captured
:endInit

%JAVACMD% %JAVA_OPTS%  -classpath %CLASSPATH% -Dapp.name="zanata-cli" -Dapp.repo="%REPO%" -Dapp.home="%BASEDIR%" -Dbasedir="%BASEDIR%" org.zanata.client.ZanataClient %CMD_LINE_ARGS%
if %ERRORLEVEL% NEQ 0 goto error
goto end

:error
if "%OS%"=="Windows_NT" @endlocal
set ERROR_CODE=%ERRORLEVEL%

:end
@REM set local scope for the variables with windows NT shell
if "%OS%"=="Windows_NT" goto endNT

@REM For old DOS remove the set variables from ENV - we assume they were not set
@REM before we started - at least we don't leave any baggage around
set CMD_LINE_ARGS=
goto postExec

:endNT
@REM If error code is set to 1 then the endlocal was done already in :error.
if %ERROR_CODE% EQU 0 @endlocal


:postExec

if "%FORCE_EXIT_ON_ERROR%" == "on" (
  if %ERROR_CODE% NEQ 0 exit %ERROR_CODE%
)

exit /B %ERROR_CODE%
