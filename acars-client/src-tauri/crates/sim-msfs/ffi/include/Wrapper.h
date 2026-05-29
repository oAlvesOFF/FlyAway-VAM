// Bindgen wrapper for the SimConnect SDK header.
//
// SimConnect.h relies on Windows types (DWORD, HRESULT, HANDLE, …)
// without including windows.h itself, so we pull it in first. Defining
// WIN32_LEAN_AND_MEAN keeps the include footprint small and bindgen's
// generated bindings file manageable.

#define WIN32_LEAN_AND_MEAN
#include <windows.h>

#include "SimConnect.h"
