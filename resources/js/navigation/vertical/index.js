import appAndPages from "./app-and-pages";
import BasicSettings from "./basic-settings";
import charts from "./charts";
import CwieSettings from "./cwie-settings";
import dashboard from "./dashboard";
import forms from "./forms";
import others from "./others";
import uiElements from "./ui-elements";

export default [
  ...dashboard,
  ...BasicSettings,
  ...CwieSettings,
  ...appAndPages,
  ...uiElements,
  ...forms,
  ...charts,
  ...others,
];
