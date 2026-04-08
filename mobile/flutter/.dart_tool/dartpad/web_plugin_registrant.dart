// Flutter web plugin registrant file.
//
// Generated file. Do not edit.
//

// @dart = 2.13
// ignore_for_file: type=lint

import 'package:fluttertoast/fluttertoast_web.dart';
import 'package:shared_preferences_web/shared_preferences_web.dart';
import 'package:sign_in_with_apple_web/sign_in_with_apple_web.dart';
import 'package:uni_links_web/uni_links_web.dart';
import 'package:flutter_web_plugins/flutter_web_plugins.dart';

void registerPlugins([final Registrar? pluginRegistrar]) {
  final Registrar registrar = pluginRegistrar ?? webPluginRegistrar;
  FluttertoastWebPlugin.registerWith(registrar);
  SharedPreferencesPlugin.registerWith(registrar);
  SignInWithApplePlugin.registerWith(registrar);
  UniLinksPlugin.registerWith(registrar);
  registrar.registerMessageHandler();
}
