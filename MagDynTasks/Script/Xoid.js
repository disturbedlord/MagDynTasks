// State Management Xoid
import { atom } from "https://esm.sh/xoid";

// Maintain Filter State
const filterObj = atom(JSON.parse(localStorage.getItem("filterObj")) || {});

// Persist Changes
filterObj.subscribe((value) => {
  localStorage.setItem("filterObj", JSON.stringify(value));
});

// Update Filter
const setFilter = (obj) => {
  filterObj.set(obj);
};

// Maintain Logged-In user State
const userObj = atom(JSON.parse(localStorage.getItem("userObj")) || {});

userObj.subscribe((value) => {
  localStorage.setItem("userObj", JSON.stringify(value));
});

// Update User State
const setUser = (obj) => {
  userObj.set(obj);
};

const destroyState = () => {
  localStorage.removeItem("userObj");
  localStorage.removeItem("filterObj");
};

window.xoid = {
  filter: {
    value: filterObj,
    setFilter: setFilter,
  },
  user: {
    value: userObj,
    setUser: setUser,
  },
  destroyState: destroyState,
};
