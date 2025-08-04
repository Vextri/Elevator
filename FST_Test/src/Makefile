# Directories
IDIR = ../include
cJSONDIR = ../include/cJSON
ODIR = obj

# Compiler and Flags
CXX = g++          # for C++ files
CC = gcc           # for C files
CFLAGS = -I$(IDIR)
LDIR = -L/usr/lib/arm-linux-gnueabihf
LIBS = -l:libmysqlcppconn.so -lpcan

# Source files
CPP_SRCS = main.cpp mainFunctions.cpp pcanFunctions.cpp databaseFunctions.cpp
OBJ = $(addprefix $(ODIR)/,$(CPP_SRCS:.cpp=.o)) $(ODIR)/cJSON.o

# Default target
all: main

# Ensure obj/ exists before compiling
$(ODIR):
	mkdir -p $(ODIR)

# Rule to compile C++ files
$(ODIR)/%.o: %.cpp | $(ODIR)
	$(CXX) -c -o $@ $< $(CFLAGS)

# Rule to compile cJSON.c using gcc
$(ODIR)/cJSON.o: $(cJSONDIR)/cJSON.c | $(ODIR)
	$(CC) -c -o $@ $< $(CFLAGS)

# Link all object files into the final executable
main: $(OBJ)
	$(CXX) -o $@ $^ $(CFLAGS) $(LDIR) $(LIBS)

# Run the program
run: main
	./main

# Clean up
clean:
	rm -f $(ODIR)/*.o main