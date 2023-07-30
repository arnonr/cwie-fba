import appAndPages from "./app-and-pages";
import BasicSettings from "./basic-settings";
import charts from "./charts";
import company from "./company";
import CwieSettings from "./cwie-settings";
import dashboard from "./dashboard";
import forms from "./forms";
import MajorHead from "./major-head";
import others from "./others";
import Staff from "./staff";
import Student from "./student";
import Teacher from "./teacher";
import uiElements from "./ui-elements";

export default [
  ...dashboard,
  ...Student,
  ...Staff,
  ...Teacher,
  ...MajorHead,
  ...company,
  ...BasicSettings,
  ...CwieSettings,
  ...appAndPages,
  ...uiElements,
  ...forms,
  ...charts,
  ...others,
];
